<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Operation;
use App\Models\RentalProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RentalDocumentController extends Controller
{
    public function store(Request $request, string $rentalId)
    {
        $rental = RentalProcess::findOrFail($rentalId);

        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', array_keys(Document::CATEGORIES)),
            'label' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/rental-' . $rental->id, 'public');

        Document::create([
            'rental_process_id' => $rental->id,
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
        }

        if ($validated['status'] === 'rejected') {
            $data['rejection_reason'] = $validated['rejection_reason'] ?? null;
        }

        $document->update($data);

        return back()->with('success', 'Estado del documento actualizado.');
    }

    public function download(string $documentId)
    {
        $document = Document::findOrFail($documentId);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
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
