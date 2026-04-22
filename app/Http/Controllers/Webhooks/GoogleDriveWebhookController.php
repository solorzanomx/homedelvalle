<?php

namespace App\Http\Controllers\Webhooks;

use App\Events\DocumentoFirmadoGoogle;
use App\Http\Controllers\Controller;
use App\Models\GoogleSignatureRequest;
use App\Services\GoogleESignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Recibe notificaciones push de cambios en Google Drive (Drive Watch API).
 *
 * Google Drive envía un POST cada vez que un archivo vigilado cambia.
 * Headers relevantes:
 *   X-Goog-Channel-Id:      channelId que registramos al hacer watchFile()
 *   X-Goog-Resource-Id:     resourceId del archivo
 *   X-Goog-Resource-State:  'sync' (primer ping) | 'update' | 'add' | 'remove'
 *   X-Goog-Channel-Token:   GOOGLE_DRIVE_WEBHOOK_SECRET para verificación
 *
 * Nota: Este webhook NO recibe el nuevo contenido del archivo, solo notifica
 * que algo cambió. Luego consultamos la eSignature API para el estado real.
 */
class GoogleDriveWebhookController extends Controller
{
    public function handle(Request $request, GoogleESignatureService $eSignature)
    {
        // Siempre responder 200 inmediatamente (Drive reintenta si no recibe 2xx)
        // El procesamiento pesado va en el event listener (asíncrono)

        $state     = $request->header('X-Goog-Resource-State');
        $channelId = $request->header('X-Goog-Channel-Id');
        $token     = $request->header('X-Goog-Channel-Token');

        // Verificar token si está configurado
        $expectedToken = config('services.google_drive.webhook_secret');
        if ($expectedToken && $token !== $expectedToken) {
            Log::warning('GoogleDriveWebhook: token inválido', [
                'channel_id' => $channelId,
                'token'      => $token,
            ]);
            return response('', 200); // Responder 200 igual, no revelar que falló
        }

        // El primer ping de Drive es solo de sincronización, ignorarlo
        if ($state === 'sync') {
            Log::info('GoogleDriveWebhook: ping de sincronización recibido', [
                'channel_id' => $channelId,
            ]);
            return response('', 200);
        }

        Log::info('GoogleDriveWebhook: notificación recibida', [
            'channel_id'  => $channelId,
            'state'       => $state,
            'resource_id' => $request->header('X-Goog-Resource-Id'),
        ]);

        // Buscar el registro correspondiente al channelId
        // (el channelId es el que generamos en watchFile() — guardado en google_response)
        $signatureRequest = GoogleSignatureRequest::whereJsonContains(
            'google_response->watch_channel_id', $channelId
        )->first();

        if (!$signatureRequest || $signatureRequest->status !== 'pending') {
            return response('', 200);
        }

        // Consultar el estado real en eSignature API
        try {
            if (empty($signatureRequest->signature_request_id)) {
                return response('', 200);
            }

            $status = $eSignature->getSignatureStatus($signatureRequest->signature_request_id);

            if ($status['status'] !== 'pending') {
                $signatureRequest->update([
                    'status'          => $status['status'],
                    'google_response' => array_merge(
                        $signatureRequest->google_response ?? [],
                        ['webhook_update' => $status['raw']]
                    ),
                    'completed_at' => $status['status'] === 'completed' ? now() : null,
                ]);

                if ($status['status'] === 'completed') {
                    DocumentoFirmadoGoogle::dispatch($signatureRequest);
                }

                Log::info('GoogleDriveWebhook: estado actualizado', [
                    'request_id' => $signatureRequest->id,
                    'status'     => $status['status'],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('GoogleDriveWebhook: error al consultar estado', [
                'channel_id' => $channelId,
                'error'      => $e->getMessage(),
            ]);
        }

        return response('', 200);
    }
}
