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
        $optionalCats  = $captacion->getApplicableOptionalDocs();
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

        $validated = $request->validate([
            'category' => 'required|string',
            'file'     => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            // Datos que ya trae el documento — se guardan directo en el
            // expediente del cliente para no volver a pedirlos ahí, mismas
            // reglas que PortalExpedienteController::saveDatos().
            'first_name'        => 'nullable|string|max:100',
            'last_name_paterno' => 'nullable|string|max:100',
            'last_name_materno' => 'nullable|string|max:100',
            'curp'              => 'nullable|string|max:18',
            'id_type'           => 'nullable|in:INE,pasaporte,cedula_profesional,otro',
            'id_number'         => 'nullable|string|max:60',
            'address_street'       => 'nullable|string|max:200',
            'address_colony'       => 'nullable|string|max:100',
            'address_municipality' => 'nullable|string|max:100',
            'address_state'        => 'nullable|string|max:60',
            'address_zip'          => 'nullable|string|max:5',
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

        $clientFields = array_filter(
            \Illuminate\Support\Arr::except($validated, ['category', 'file']),
            fn ($value) => $value !== null && $value !== ''
        );
        if (!empty($clientFields)) {
            if (!empty($clientFields['first_name']) && !empty($clientFields['last_name_paterno'])) {
                $clientFields['name'] = trim(
                    ($clientFields['first_name'] ?? '') . ' ' .
                    ($clientFields['last_name_paterno'] ?? '') . ' ' .
                    ($clientFields['last_name_materno'] ?? '')
                );
            }
            $client->update($clientFields);
        }

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
