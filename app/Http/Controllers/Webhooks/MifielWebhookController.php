<?php

namespace App\Http\Controllers\Webhooks;

use App\Events\DocumentoFirmado;
use App\Http\Controllers\Controller;
use App\Services\MifielService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MifielWebhookController extends Controller
{
    public function __construct(private MifielService $mifiel) {}

    /**
     * Recibe notificaciones de Mifiel cuando un documento cambia de estado.
     * Siempre retorna HTTP 200 — si devolvemos otro código, Mifiel reintenta.
     */
    public function handle(Request $request): Response
    {
        // 1. Verificar autenticidad
        if (!$this->mifiel->verifyWebhook($request)) {
            Log::warning('MifielWebhook: firma inválida — ignorando', [
                'ip'      => $request->ip(),
                'payload' => substr($request->getContent(), 0, 200),
            ]);
            // Retornamos 200 de todos modos para evitar reintentos innecesarios
            return response('Unauthorized', 200);
        }

        $payload = $request->all();

        Log::info('MifielWebhook: payload recibido', [
            'document_id' => $payload['document']['id'] ?? $payload['id'] ?? null,
            'status'      => $payload['document']['status'] ?? $payload['status'] ?? null,
        ]);

        try {
            $this->dispatch($payload);
        } catch (\Throwable $e) {
            // Nunca lanzamos excepción — logueamos y devolvemos 200
            Log::error('MifielWebhook: error procesando payload', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        return response('OK', 200);
    }

    // ── Despacho por estado ───────────────────────────────────────────────

    private function dispatch(array $payload): void
    {
        // Mifiel puede enviar el documento anidado o en la raíz
        $doc    = $payload['document'] ?? $payload;
        $docId  = $doc['id'] ?? null;
        $status = $doc['status'] ?? null;

        if (!$docId) {
            Log::warning('MifielWebhook: payload sin document_id');
            return;
        }

        if ($status === 'signed') {
            DocumentoFirmado::dispatch($docId, $payload);

            Log::info('MifielWebhook: evento DocumentoFirmado despachado', [
                'document_id' => $docId,
            ]);
            return;
        }

        // Otros estados (declined, expired, etc.) — solo log por ahora
        Log::info('MifielWebhook: estado no manejado', [
            'document_id' => $docId,
            'status'      => $status,
        ]);
    }
}
