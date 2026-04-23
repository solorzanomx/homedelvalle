<?php

namespace App\Listeners;

use App\Events\DocumentoFirmadoGoogle;
use App\Services\ClientPortalService;
use App\Services\GoogleDriveService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcesarDocumentoFirmadoGoogle implements ShouldQueue
{
    public function handle(DocumentoFirmadoGoogle $event): void
    {
        $request = $event->signatureRequest;

        Log::info('DocumentoFirmadoGoogle: procesando documento firmado', [
            'request_id'    => $request->id,
            'document_name' => $request->document_name,
            'file_id'       => $request->file_id,
            'tipo'          => $request->tipo,
            'contacto_id'   => $request->contacto_id,
        ]);

        // Descargar el PDF firmado de Drive
        try {
            $drive = app(GoogleDriveService::class);

            $fileName    = 'contrato_' . $request->id . '_firmado.pdf';
            $destination = storage_path('app/contratos/' . $fileName);

            $downloaded = $drive->downloadSignedDocument($request->file_id, $destination);

            if ($downloaded) {
                $request->update([
                    'local_pdf_path' => 'contratos/' . $fileName,
                ]);

                Log::info('DocumentoFirmadoGoogle: PDF firmado descargado', [
                    'request_id'  => $request->id,
                    'destination' => $destination,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('DocumentoFirmadoGoogle: error al descargar PDF', [
                'request_id' => $request->id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Si es contrato de confidencialidad: crear acceso al portal del cliente
        if ($request->tipo === 'confidencialidad' && $request->contacto) {
            $client = $request->contacto;

            // Solo crear si aún no tiene acceso
            if (!$client->user_id) {
                try {
                    $portalService = app(ClientPortalService::class);
                    $result = $portalService->createPortalAccount($client);

                    Log::info('DocumentoFirmadoGoogle: portal creado tras firma', [
                        'client_id' => $client->id,
                        'email'     => $client->email,
                    ]);

                    // Enviar WhatsApp con credenciales
                    $phone = $client->whatsapp ?? $client->phone ?? null;
                    if ($phone && isset($result['password'])) {
                        try {
                            app(WhatsAppService::class)->send(
                                $phone,
                                "¡Hola {$client->name}! Firmaste tu contrato de confidencialidad. " .
                                'Ya tienes acceso a tu portal de valuación en: ' . url('/portal') .
                                ' — Usuario: ' . $client->email .
                                ' — Contraseña: ' . $result['password']
                            );
                        } catch (\Throwable $e) {
                            Log::warning('DocumentoFirmadoGoogle: no se pudo enviar WhatsApp de bienvenida', [
                                'client_id' => $client->id,
                                'error'     => $e->getMessage(),
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    Log::error('DocumentoFirmadoGoogle: error al crear portal', [
                        'client_id' => $client->id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

