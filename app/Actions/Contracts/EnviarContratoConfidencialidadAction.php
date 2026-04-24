<?php

namespace App\Actions\Contracts;

use App\Models\GoogleSignatureRequest;
use App\Services\GoogleESignatureService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class EnviarContratoConfidencialidadAction
{
    public function __construct(
        private GoogleESignatureService $eSignature,
        private WhatsAppService         $whatsapp,
    ) {}

    public function execute(GoogleSignatureRequest $record): GoogleSignatureRequest
    {
        $client = $record->contacto;

        // 1. Enviar solicitud de firma electrónica
        try {
            $result = $this->eSignature->requestSignature($record->file_id, $record->signers);

            $record->update([
                'signature_request_id' => $result['signature_request_id'],
                'google_response'      => $result['raw'] ?? $result,
                'status'               => 'pending',
            ]);
        } catch (\Throwable $e) {
            Log::error('EnviarContratoConfidencialidad: error en eSignature', [
                'record_id' => $record->id,
                'file_id'   => $record->file_id,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }

        // 2. Notificar al cliente por WhatsApp
        $phone = $client->whatsapp ?? $client->phone ?? null;
        if ($phone) {
            try {
                $this->whatsapp->send(
                    $phone,
                    "Hola {$client->name}, te hemos enviado un correo para firmar tu contrato de confidencialidad. " .
                    'Por favor revísalo para continuar con el proceso de valuación.'
                );
            } catch (\Throwable $e) {
                Log::warning('EnviarContratoConfidencialidad: no se pudo enviar WhatsApp', [
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return $record->fresh();
    }
}
