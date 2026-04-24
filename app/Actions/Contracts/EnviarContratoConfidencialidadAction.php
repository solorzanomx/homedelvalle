<?php

namespace App\Actions\Contracts;

use App\Models\GoogleSignatureRequest;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class EnviarContratoConfidencialidadAction
{
    public function __construct(
        private WhatsAppService $whatsapp,
    ) {}

    /**
     * Marca el contrato como enviado (pending).
     * El admin envía el documento manualmente desde Google Docs.
     * Se notifica al cliente por WhatsApp para que esté pendiente del correo.
     */
    public function execute(GoogleSignatureRequest $record): GoogleSignatureRequest
    {
        $client = $record->contacto;

        $record->update(['status' => 'pending']);

        $phone = $client->whatsapp ?? $client->phone ?? null;
        if ($phone) {
            try {
                $this->whatsapp->send(
                    $phone,
                    "Hola {$client->name}, te hemos enviado un correo con tu contrato de confidencialidad para firmar. " .
                    'Por favor revísalo para continuar con el proceso de valuación de tu inmueble.'
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
