<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\CaptacionService;
use App\Services\ClientPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PortalCaptacionController extends Controller
{
    public function __construct(
        protected ClientPortalService $portalService,
        protected CaptacionService $captacionService,
    ) {}

    public function show()
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) {
            return redirect()->route('portal.dashboard');
        }

        $captacion = $this->captacionService->getOrCreateForClient($client);
        $captacion->load(['documents', 'valuation', 'signatureRequest']);

        $allCategories = \App\Models\Document::CATEGORIES;
        $requiredCats  = \App\Models\Captacion::REQUIRED_DOCS_ETAPA1;
        $optionalCats  = \App\Models\Captacion::OPTIONAL_DOCS_ETAPA1;
        $showCats      = array_merge($requiredCats, $optionalCats);

        // Map category → uploaded documents for this captacion
        $docsByCategory = $captacion->documents->groupBy('category');

        return view('portal.captacion.show', compact('client', 'captacion', 'allCategories', 'requiredCats', 'optionalCats', 'showCats', 'docsByCategory'));
    }

    public function uploadDocument(Request $request)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) {
            return redirect()->route('portal.dashboard');
        }

        $request->validate([
            'category' => 'required|string',
            'file'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $captacion = $this->captacionService->getOrCreateForClient($client);

        $path     = $request->file('file')->store('captaciones/' . $captacion->id, 'public');
        $original = $request->file('file')->getClientOriginalName();

        Document::create([
            'captacion_id'         => $captacion->id,
            'client_id'            => $client->id,
            'uploaded_by'          => Auth::id(),
            'category'             => $request->input('category'),
            'label'                => $request->input('label') ?: \App\Models\Document::CATEGORIES[$request->input('category')] ?? $request->input('category'),
            'file_path'            => $path,
            'file_name'            => $original,
            'mime_type'            => $request->file('file')->getMimeType(),
            'file_size'            => $request->file('file')->getSize(),
            'status'               => 'pending',
            'is_captacion_required'=> in_array($request->input('category'), \App\Models\Captacion::REQUIRED_DOCS_ETAPA1),
            'captacion_status'     => 'pendiente',
        ]);

        return back()->with('success', 'Documento subido. Tu asesor lo revisará pronto.');
    }

    public function deleteDocument(Document $document)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client || $document->client_id !== $client->id) {
            abort(403);
        }
        // Only allow deleting documents not yet approved
        if ($document->captacion_status === 'aprobado') {
            return back()->with('error', 'No puedes eliminar un documento ya aprobado.');
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Documento eliminado.');
    }

    public function confirmPriceAgreement(Request $request)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) {
            return redirect()->route('portal.dashboard');
        }

        $captacion = $this->captacionService->getOrCreateForClient($client);

        if ($captacion->portal_etapa < 3) {
            return back()->with('error', 'No es posible confirmar el precio en esta etapa.');
        }

        if (!$captacion->precio_acordado) {
            return back()->with('error', 'Tu asesor debe establecer el precio antes de que puedas confirmarlo.');
        }

        // Record client acceptance timestamp
        $captacion->update(['etapa3_completed_at' => now()]);
        $this->captacionService->recalculateStage($captacion);

        return back()->with('success', 'Precio confirmado. Ahora procederemos con el contrato de exclusiva.');
    }
}
