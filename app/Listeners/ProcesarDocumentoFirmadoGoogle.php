<?php

namespace App\Listeners;

use App\Events\DocumentoFirmadoGoogle;
use App\Services\GoogleDriveService;
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

        // TODO: conectar al CRM cuando sea necesario
        // Ejemplo futuro:
        //   $client = $request->contacto;
        //   $client->activities()->create([...]);
        //   Notification::send($client, new ContratoFirmadoNotification($request));
    }
}
