<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Models\Document;
use App\Models\RentalProcess;
use App\Services\ClientPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PortalDocumentController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function index()
    {
        $client = $this->portalService->getClientForUser(Auth::user());

        if (!$client) {
            return view('portal.documents.index', [
                'documents'          => collect(),
                'captacionDocuments' => collect(),
                'captacion'          => null,
                'client'             => null,
                'allCategories'      => Document::CATEGORIES,
            ]);
        }

        // Inquilino con renta activa: lista simple de lo que le toca subir, con estado por documento.
        if ($tenantRental = $this->portalService->activeTenantRental($client)) {
            $rows = \App\Support\TenantDocumentRows::build($tenantRental, $client, request('open'));

            return view('portal.documents.tenant', [
                'client' => $client,
                'rental' => $tenantRental,
                'groups' => $rows['groups'],
                'counts' => $rows['counts'],
                'next' => $rows['next'],
                'open' => request('open'),
                'openCat' => request('cat'),
            ]);
        }

        // Captacion del cliente y sus documentos — sin filtrar status (mismo
        // bug ya corregido en EnsurePortalLegalAcceptance/PortalDashboardController).
        $captacion = Captacion::where('client_id', $client->id)
            ->with('documents')
            ->latest()
            ->first();

        $captacionDocuments   = $captacion ? $captacion->documents->sortBy('category') : collect();
        $captacionDocumentIds = $captacionDocuments->pluck('id')->all();

        // General documents (rental, general) — excluir los de captación para evitar duplicados
        $documents = $this->portalService->getDocumentsForClient($client)
            ->filter(fn($d) => !in_array($d->id, $captacionDocumentIds))
            ->values();

        return view('portal.documents.index', compact(
            'documents', 'captacionDocuments', 'captacion', 'client'
        ) + ['allCategories' => Document::CATEGORIES]);
    }

    /**
     * ¿El cliente autenticado puede ver este documento? Suyo (client_id) siempre. Documentos de una
     * captación/renta SIN dueño individual (contratos, recibos): cualquiera de las partes. Los documentos
     * PERSONALES de otra parte (INE, estados de cuenta del inquilino) NO se comparten con el propietario.
     */
    private function authorizedDocument(string $id): Document
    {
        $client   = $this->portalService->getClientForUser(Auth::user());
        $document = Document::findOrFail($id);

        $hasAccess = false;
        if ($client) {
            if ($document->client_id === $client->id) {
                $hasAccess = true;
            } elseif (! $document->client_id && $document->captacion_id) {
                $cap = Captacion::find($document->captacion_id);
                $hasAccess = $cap && $cap->client_id === $client->id;
            } elseif (! $document->client_id && $document->rental_process_id) {
                $rental = RentalProcess::find($document->rental_process_id);
                $hasAccess = $rental && ($rental->owner_client_id === $client->id || $rental->tenant_client_id === $client->id);
            } elseif ($document->captacion_id) {
                $cap = Captacion::find($document->captacion_id);
                $hasAccess = $cap && $cap->client_id === $client->id;
            }
        }

        abort_unless($hasAccess, 403, 'No tienes acceso a este documento.');

        return $document;
    }

    public function download(string $id)
    {
        $document = $this->authorizedDocument($id);
        \App\Models\DocumentEvent::logAccess($document, 'descargado desde el Portal');

        return \App\Support\SecureFiles::response($document->file_path, $document->file_name, $document->mime_type)
            ?? back()->with('error', 'Archivo no encontrado.');
    }

    /** Vista en línea (miniaturas y visor del Portal) — mismas reglas de acceso que la descarga. */
    public function preview(Request $request, string $id)
    {
        $document = $this->authorizedDocument($id);

        if ($request->boolean('thumb') && ($thumb = \App\Support\SecureFiles::thumbnailResponse($document->file_path))) {
            return $thumb;
        }

        return \App\Support\SecureFiles::response($document->file_path, $document->file_name, $document->mime_type, true)
            ?? abort(404);
    }

    public function upload(Request $request)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) abort(403);

        $validated = $request->validate([
            'category'          => 'required|string',
            'label'             => 'nullable|string|max:120',
            'rental_process_id' => 'nullable|integer',
            'file'              => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file  = $request->file('file');

        $quality = app(\App\Services\DocumentQualityService::class);
        $gate = $quality->gate($file, $validated['category'], $client->id);
        if ($gate['block']) {
            return back()->with('error', $gate['block']);
        }

        $path  = \App\Support\SecureFiles::store($file, 'documents/client-' . $client->id);
        $label = !empty($validated['label']) ? $validated['label'] : $file->getClientOriginalName();

        $document = Document::create([
            'client_id'         => $client->id,
            'rental_process_id' => $validated['rental_process_id'] ?? null,
            'uploaded_by'       => Auth::id(),
            'category'          => $validated['category'],
            'label'             => $label,
            'file_path'         => $path,
            'file_name'         => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'status'            => 'received',
        ]);

        $quality->record($document, $gate);

        return back()->with('success', 'Documento subido correctamente.');
    }
}
