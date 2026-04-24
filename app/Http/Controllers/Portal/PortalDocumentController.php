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

        // Captacion activa y sus documentos
        $captacion = Captacion::where('client_id', $client->id)
            ->where('status', 'activo')
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

    public function download(string $id)
    {
        $client   = $this->portalService->getClientForUser(Auth::user());
        $document = Document::findOrFail($id);

        $hasAccess = false;
        if ($client) {
            if ($document->client_id === $client->id) {
                $hasAccess = true;
            } elseif ($document->captacion_id) {
                $cap = Captacion::find($document->captacion_id);
                if ($cap && $cap->client_id === $client->id) {
                    $hasAccess = true;
                }
            } elseif ($document->rental_process_id) {
                $rental = RentalProcess::find($document->rental_process_id);
                if ($rental && ($rental->owner_client_id === $client->id || $rental->tenant_client_id === $client->id)) {
                    $hasAccess = true;
                }
            }
        }

        if (!$hasAccess) {
            abort(403, 'No tienes acceso a este documento.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function upload(Request $request)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) abort(403);

        $validated = $request->validate([
            'category' => 'required|string',
            'file'     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/client-' . $client->id, 'public');

        Document::create([
            'client_id'   => $client->id,
            'uploaded_by' => Auth::id(),
            'category'    => $validated['category'],
            'label'       => $file->getClientOriginalName(),
            'file_path'   => $path,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'file_size'   => $file->getSize(),
            'status'      => 'received',
        ]);

        return back()->with('success', 'Documento subido correctamente.');
    }
}
