<?php

namespace App\Listeners;

use App\Events\DocumentoFirmado;
use App\Models\MifielDocumento;
use App\Services\MifielService;
use Illuminate\Support\Facades\Log;

class ProcesarDocumentoFirmado
{
    public function __construct(private MifielService $mifiel) {}

    public function handle(DocumentoFirmado $event): void
    {
        Log::info('ProcesarDocumentoFirmado: documento firmado recibido', [
            'document_id' => $event->documentId,
            'metadata'    => $event->metadata,
        ]);

        // Actualizar registro en BD si existe
        $registro = MifielDocumento::where('document_id', $event->documentId)->first();

        if (!$registro) {
            Log::warning('ProcesarDocumentoFirmado: no se encontró registro local', [
                'document_id' => $event->documentId,
            ]);
            return;
        }

        // Descargar PDF firmado
        $destinationPath = 'mifiel/firmados/' . $event->documentId . '.pdf';

        try {
            $downloaded = $this->mifiel->downloadSigned($event->documentId, $destinationPath);

            $registro->update([
                'status'          => 'signed',
                'pdf_path'        => $downloaded ? $destinationPath : null,
                'mifiel_response' => $event->metadata,
                'signed_at'       => now(),
            ]);

            Log::info('ProcesarDocumentoFirmado: registro actualizado', [
                'document_id'  => $event->documentId,
                'pdf_path'     => $destinationPath,
                'downloaded'   => $downloaded,
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcesarDocumentoFirmado: error al descargar PDF firmado', [
                'document_id' => $event->documentId,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
