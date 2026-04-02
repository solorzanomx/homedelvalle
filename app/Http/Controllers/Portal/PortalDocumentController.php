<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
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
            return view('portal.documents.index', ['documents' => collect(), 'client' => null]);
        }

        $documents = $this->portalService->getDocumentsForClient($client);

        return view('portal.documents.index', compact('documents', 'client'));
    }

    public function download(string $id)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $document = Document::findOrFail($id);

        // Verify access: document belongs to client or their rental
        $hasAccess = false;
        if ($client) {
            if ($document->client_id === $client->id) {
                $hasAccess = true;
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

        if (!$client) {
            abort(403);
        }

        $validated = $request->validate([
            'rental_process_id' => 'required|exists:rental_processes,id',
            'category' => 'required|string',
            'label' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Verify client has access to this rental
        $rental = RentalProcess::findOrFail($validated['rental_process_id']);
        if ($rental->owner_client_id !== $client->id && $rental->tenant_client_id !== $client->id) {
            abort(403);
        }

        $file = $request->file('file');
        $path = $file->store('documents/rental-' . $rental->id, 'public');

        Document::create([
            'rental_process_id' => $rental->id,
            'client_id' => $client->id,
            'uploaded_by' => Auth::id(),
            'category' => $validated['category'],
            'label' => $validated['label'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'received',
        ]);

        return back()->with('success', 'Documento subido exitosamente.');
    }
}
