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
        $path = \App\Support\SecureFiles::store($file, 'documents/rental-' . $rental->id);

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
        $path = \App\Support\SecureFiles::store($file, 'documents/operation-' . $operation->id);

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

        app(\App\Services\DocumentReviewService::class)->apply($document, $validated['status'], $validated['rejection_reason'] ?? null);
        $document->refresh();

        // El visor del CRM aprueba/rechaza sin recargar la página.
        // El asesor puede pedir no avisar (casilla del visor): queda a mano para WhatsApp o "avisar ahora".
        $notifier = app(\App\Services\DocumentRejectionNotifier::class);
        $skipped = $validated['status'] === 'rejected' && $request->input('notify_client') === '0';
        if ($skipped) {
            $notifier->markSkipped($document);
        }

        if ($request->expectsJson()) {
            $notice = null;
            $client = $document->client;
            if ($validated['status'] === 'rejected' && $client) {
                $notice = [
                    'client' => $client->name,
                    'pending' => $notifier->pendingFor($client)->count(),
                    'auto' => ! $skipped && (bool) $client->email,
                    'minutes' => \App\Services\DocumentRejectionNotifier::DEFAULT_DELAY_MINUTES,
                    'can_email' => (bool) $client->email,
                    'can_whatsapp' => strlen(preg_replace('/[^0-9]/', '', $client->whatsapp ?: $client->phone ?: '')) >= 10,
                ];
            }

            return response()->json([
                'ok' => true,
                'status' => $document->status,
                'status_label' => $document->status_label,
                'rejection_reason' => $document->rejection_reason,
                'notice' => $notice,
            ]);
        }

        return back()->with('success', 'Estado del documento actualizado.');
    }

    public function download(string $documentId)
    {
        $document = Document::findOrFail($documentId);

        \App\Models\DocumentEvent::logAccess($document, 'descargado');

        return \App\Support\SecureFiles::response($document->file_path, $document->file_name, $document->mime_type)
            ?? back()->with('error', 'Archivo no encontrado.');
    }

    /** "Avisar ahora" por correo o WhatsApp a quien tiene documentos rechazados (todos los del cliente, en un solo mensaje). */
    public function notifyRejection(Request $request, string $documentId)
    {
        $request->validate(['channel' => 'required|in:email,whatsapp']);

        $client = Document::with('client')->findOrFail($documentId)->client;
        if (! $client) {
            return response()->json(['ok' => false, 'message' => 'El documento no está ligado a un cliente.'], 422);
        }

        $notifier = app(\App\Services\DocumentRejectionNotifier::class);
        $docs = $notifier->pendingFor($client);
        if ($docs->isEmpty()) {
            return response()->json(['ok' => false, 'message' => 'No hay documentos rechazados pendientes de avisar.'], 422);
        }

        if ($request->input('channel') === 'whatsapp') {
            $url = $notifier->whatsappUrl($client, $docs);

            return $url
                ? response()->json(['ok' => true, 'url' => $url, 'count' => $docs->count()])
                : response()->json(['ok' => false, 'message' => 'El cliente no tiene un teléfono válido.'], 422);
        }

        $sent = $notifier->sendEmail($client, $docs, Auth::user());

        return response()->json([
            'ok' => $sent,
            'count' => $docs->count(),
            'message' => $sent ? "Correo enviado a {$client->email}." : ($client->email ? 'No se pudo enviar el correo — revisa la configuración de correo saliente.' : 'El cliente no tiene correo registrado.'),
        ], $sent ? 200 : 422);
    }

    /** Aprobar varios documentos a la vez (bandeja central). Solo aprueba; rechazar siempre es individual y con motivo. */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate(['ids' => 'required|array|min:1|max:200', 'ids.*' => 'integer']);

        $result = app(\App\Services\DocumentReviewService::class)->bulkApprove($validated['ids']);

        return response()->json(['ok' => true] + $result);
    }

    /** Abre el archivo en el navegador (visor del CRM) en vez de forzar la descarga. */
    public function preview(Request $request, string $documentId)
    {
        $document = Document::findOrFail($documentId);

        // ?thumb=1 → miniatura reducida para las listas (cae a la imagen completa si no se puede generar).
        if ($request->boolean('thumb') && ($thumb = \App\Support\SecureFiles::thumbnailResponse($document->file_path))) {
            return $thumb;
        }

        \App\Models\DocumentEvent::logAccess($document, 'visto');

        return \App\Support\SecureFiles::response($document->file_path, $document->file_name, $document->mime_type, true) ?? abort(404, 'Archivo no encontrado.');
    }

    public function destroy(string $documentId)
    {
        $document = Document::findOrFail($documentId);

        \App\Support\SecureFiles::delete($document->file_path);

        $document->delete();

        return back()->with('success', 'Documento eliminado.');
    }
}
