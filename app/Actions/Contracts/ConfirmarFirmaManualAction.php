<?php

namespace App\Actions\Contracts;

use App\Models\GoogleSignatureRequest;
use App\Services\ClientPortalService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class ConfirmarFirmaManualAction
{
    public function __construct(
        private ClientPortalService $portal,
        private WhatsAppService     $whatsapp,
    ) {}

    /**
     * El admin confirma manualmente que el cliente firmó el contrato.
     * Marca como completado, crea acceso al portal y notifica al cliente.
     */
    public function execute(GoogleSignatureRequest $record): GoogleSignatureRequest
    {
        $client = $record->contacto;

        $record->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Crear acceso al portal si no tiene uno
        if (!$client->user_id) {
            try {
                $result = $this->portal->createPortalAccount($client);

                $phone = $client->whatsapp ?? $client->phone ?? null;
                if ($phone) {
                    $this->whatsapp->send(
                        $phone,
                        "¡Hola {$client->name}! Hemos recibido tu contrato firmado. " .
                        'Ya tienes acceso a tu portal en ' . url('/portal') . ' — ' .
                        "usuario: {$client->email}, contraseña: {$result['password']}"
                    );
                }
            } catch (\Throwable $e) {
                Log::error('ConfirmarFirmaManual: error creando portal', [
                    'record_id' => $record->id,
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        return $record->fresh();
    }
}
