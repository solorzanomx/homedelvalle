<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Operation;
use App\Models\RentalProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RentalDocumentController extends Controller
{
    public function store(Request $request, string $rentalId)
    {
        $rental = RentalProcess::findOrFail($rentalId);

        // client_id opcional aquí — se guarda solo si el documento es de una
        // de las 2 partes del trato (inquilino/propietario). Antes NUNCA se
        // guardaba, así que un documento subido desde el CRM (identificación,
        // comprobante de domicilio) no coincidía con lo que busca el Portal
        // del Cliente (Document.client_id) y no se veía ahí — ni al revés
        // (hallazgo 2026-09-24).
        $validClientIds = array_values(array_filter([$rental->tenant_client_id, $rental->owner_client_id]));

        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', array_keys(Document::CATEGORIES)),
            'label' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'client_id' => ['nullable', Rule::in($validClientIds)],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/rental-' . $rental->id, 'public');

        Document::create([
            'rental_process_id' => $rental->id,
            'client_id' => $validated['client_id'] ?? null,
            'uploaded_by' => Auth::id(),
            'category' => $validated['category'],
            'label' => $validated['label'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'received',
        ]);

        return back()->with('success', 'Documento subido correctamente.');
    }

    public function storeForOperation(Request $request, string $operationId)
    {
        $operation = Operation::findOrFail($operationId);

        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', array_keys(Document::CATEGORIES)),
            'label' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/operation-' . $operation->id, 'public');

        Document::create([
            'operation_id' => $operation->id,
            'uploaded_by' => Auth::id(),
            'category' => $validated['category'],
            'label' => $validated['label'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'received',
        ]);

        return back()->with('success', 'Documento subido correctamente.');
    }

    public function updateStatus(Request $request, string $documentId)
    {
        $document = Document::findOrFail($documentId);

        $validated = $request->validate([
            'status' => 'required|in:pending,received,verified,rejected',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'verified') {
            $data['verified_at'] = now();
            $data['verified_by'] = Auth::id();
            $data['rejection_reason'] = null;
        }

        if ($validated['status'] === 'rejected') {
            $data['rejection_reason'] = $validated['rejection_reason'] ?? null;
        }

        $document->update($data);

        // El visor del CRM aprueba/rechaza sin recargar la página.
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $document->status,
                'status_label' => $document->status_label,
                'rejection_reason' => $document->rejection_reason,
            ]);
        }

        return back()->with('success', 'Estado del documento actualizado.');
    }

    public function download(string $documentId)
    {
        $document = Document::findOrFail($documentId);

        // Presentaciones PDF se almacenan con ruta absoluta fuera del disco público
        if (str_starts_with($document->file_path, '/') || str_starts_with($document->file_path, storage_path())) {
            if (!file_exists($document->file_path)) {
                return back()->with('error', 'Archivo no encontrado.');
            }
            return response()->download($document->file_path, $document->file_name ?? basename($document->file_path));
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /** Abre el archivo en el navegador (visor del CRM) en vez de forzar la descarga. */
    public function preview(string $documentId)
    {
        $document = Document::findOrFail($documentId);

        $absolute = (str_starts_with($document->file_path, '/') || str_starts_with($document->file_path, storage_path()))
            ? $document->file_path
            : (Storage::disk('public')->exists($document->file_path) ? Storage::disk('public')->path($document->file_path) : null);

        if (! $absolute || ! file_exists($absolute)) {
            abort(404, 'Archivo no encontrado.');
        }

        return response()->file($absolute, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->file_name ?? basename($absolute)) . '"',
        ]);
    }

    public function destroy(string $documentId)
    {
        $document = Document::findOrFail($documentId);

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Documento eliminado.');
    }
}
