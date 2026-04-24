<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Models\Document;
use App\Models\PropertyValuation;
use App\Services\CaptacionService;
use Illuminate\Http\Request;

class CaptacionAdminController extends Controller
{
    public function __construct(protected CaptacionService $service) {}

    public function index()
    {
        $etapaLabels = [1 => 'Documentación', 2 => 'Valuación', 3 => 'Precio', 4 => 'Exclusiva'];
        $etapaColors = [1 => '#f59e0b', 2 => '#3b82f6', 3 => '#8b5cf6', 4 => '#10b981'];

        $all = Captacion::with(['client', 'documents'])
            ->where('status', 'activo')
            ->latest()
            ->get();

        $byEtapa = $all->groupBy('portal_etapa');

        $stats = [
            'total'          => $all->count(),
            'pipeline_value' => $all->whereNotNull('precio_acordado')->sum('precio_acordado'),
            'docs_pending'   => \App\Models\Document::where('captacion_status', 'pendiente')->whereNotNull('captacion_id')->count(),
        ];

        // For table view paginated
        $captaciones = Captacion::with(['client', 'documents'])
            ->where('status', 'activo')
            ->latest()
            ->paginate(20);

        return view('admin.captaciones.index', compact(
            'captaciones', 'byEtapa', 'etapaLabels', 'etapaColors', 'stats'
        ));
    }

    public function show(Captacion $captacion)
    {
        $captacion->load(['client', 'documents.uploader', 'valuation', 'signatureRequest']);

        $allCategories  = Document::CATEGORIES;
        $requiredCats   = Captacion::REQUIRED_DOCS_ETAPA1;
        $optionalCats   = Captacion::OPTIONAL_DOCS_ETAPA1;
        $docsByCategory = $captacion->documents->groupBy('category');

        // Valuaciones vinculadas a propiedades del cliente
        $propertyIds = \App\Models\Property::where('client_id', $captacion->client_id)->pluck('id');
        $valuations  = PropertyValuation::whereIn('property_id', $propertyIds)->latest()->get();

        // Timeline: interactions del cliente
        $interactions = \App\Models\Interaction::where('client_id', $captacion->client_id)
            ->with('user')
            ->latest()
            ->take(30)
            ->get();

        $etapaLabels = [1 => 'Documentación', 2 => 'Valuación', 3 => 'Precio', 4 => 'Exclusiva'];
        $etapaColors = [1 => '#f59e0b', 2 => '#3b82f6', 3 => '#8b5cf6', 4 => '#10b981'];

        return view('admin.captaciones.show', compact(
            'captacion', 'allCategories', 'requiredCats', 'optionalCats',
            'docsByCategory', 'valuations', 'interactions', 'etapaLabels', 'etapaColors'
        ));
    }

    public function updateDocStatus(Request $request, Captacion $captacion, Document $document)
    {
        $request->validate(['captacion_status' => 'required|in:aprobado,rechazado,pendiente', 'rejection_reason' => 'nullable|string|max:500']);

        if ($request->captacion_status === 'aprobado') {
            $this->service->approveDocument($document);
        } elseif ($request->captacion_status === 'rechazado') {
            $this->service->rejectDocument($document, $request->input('rejection_reason'));
        } else {
            $document->update(['captacion_status' => 'pendiente', 'rejection_reason' => null]);
        }

        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Estado del documento actualizado.');
    }

    public function linkValuation(Request $request, Captacion $captacion)
    {
        $request->validate(['valuation_id' => 'required|exists:property_valuations,id']);

        $this->service->linkValuation($captacion, $request->input('valuation_id'));

        return back()->with('success', 'Valuación vinculada. El cliente avanza a la etapa de precio.');
    }

    public function setPrice(Request $request, Captacion $captacion)
    {
        $request->validate(['precio' => 'required|numeric|min:1']);

        $this->service->confirmPrice($captacion, $request->input('precio'));

        return back()->with('success', 'Precio establecido. El cliente podrá confirmarlo en su portal.');
    }

    public function generarExclusiva(Captacion $captacion)
    {
        if (!$captacion->precio_acordado) {
            return back()->with('error', 'Establece el precio antes de generar el contrato de exclusiva.');
        }

        try {
            $action = app(\App\Actions\Contracts\GenerarContratoExclusivaAction::class);
            $signatureRequest = $action->execute($captacion->client, $captacion);
            $this->service->linkExclusiva($captacion, $signatureRequest->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar contrato: ' . $e->getMessage());
        }

        return back()->with('success', 'Contrato de exclusiva generado en Drive.');
    }

    public function markExclusivaSigned(Captacion $captacion)
    {
        if (!$captacion->etapa4_signature_id) {
            return back()->with('error', 'No hay contrato de exclusiva generado.');
        }

        $captacion->signatureRequest->update(['status' => 'completed', 'completed_at' => now()]);
        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Contrato marcado como firmado. Proceso completado.');
    }

    public function uploadDocument(Request $request, Captacion $captacion)
    {
        $request->validate([
            'category' => 'required|string',
            'file'     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/captacion-' . $captacion->id, 'public');

        Document::create([
            'captacion_id'     => $captacion->id,
            'client_id'        => $captacion->client_id,
            'uploaded_by'      => \Illuminate\Support\Facades\Auth::id(),
            'category'         => $request->category,
            'label'            => $file->getClientOriginalName(),
            'file_path'        => $path,
            'file_name'        => $file->getClientOriginalName(),
            'mime_type'        => $file->getMimeType(),
            'file_size'        => $file->getSize(),
            'captacion_status' => 'pendiente',
            'status'           => 'received',
        ]);

        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Documento subido correctamente.');
    }

    public function deleteDocument(Captacion $captacion, Document $document)
    {
        if ($document->captacion_id !== $captacion->id) abort(403);

        if ($document->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Documento eliminado.');
    }
}
