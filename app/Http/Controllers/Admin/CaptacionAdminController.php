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
        $captaciones = Captacion::with(['client', 'documents', 'valuation', 'signatureRequest'])
            ->where('status', 'activo')
            ->latest()
            ->paginate(20);

        return view('admin.captaciones.index', compact('captaciones'));
    }

    public function show(Captacion $captacion)
    {
        $captacion->load(['client', 'documents.uploader', 'valuation', 'signatureRequest']);

        $allCategories  = Document::CATEGORIES;
        $requiredCats   = Captacion::REQUIRED_DOCS_ETAPA1;
        $docsByCategory = $captacion->documents->groupBy('category');

        // Valuaciones vinculadas a propiedades del cliente (si tiene propiedades)
        $propertyIds = \App\Models\Property::where('client_id', $captacion->client_id)->pluck('id');
        $valuations  = PropertyValuation::whereIn('property_id', $propertyIds)
            ->latest()
            ->get();

        return view('admin.captaciones.show', compact('captacion', 'allCategories', 'requiredCats', 'docsByCategory', 'valuations'));
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
}
