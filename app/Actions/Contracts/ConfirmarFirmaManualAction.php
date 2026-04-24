<?php

namespace App\Actions\Contracts;

use App\Models\GoogleSignatureRequest;
use App\Models\Interaction;
use App\Services\EmailService;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Log;

class ConfirmarFirmaManualAction
{
    public function __construct(
        private ClientPortalService $portal,
        private EmailService        $email,
    ) {}

    public function execute(GoogleSignatureRequest $record): GoogleSignatureRequest
    {
        $client = $record->contacto;

        $record->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Crear acceso al portal si no tiene uno
        $result = $this->portal->createPortalAccount($client);

        // Enviar correo de bienvenida con credenciales del portal
        if ($result['password']) {
            try {
                $this->email->sendPortalWelcome($client->name, $client->email, $result['password']);
            } catch (\Throwable $e) {
                Log::warning('ConfirmarFirmaManual: no se pudo enviar correo de bienvenida', [
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // Registrar en el timeline del cliente
        Interaction::create([
            'client_id'   => $client->id,
            'user_id'     => auth()->id(),
            'type'        => 'note',
            'description' => 'Contrato de Confidencialidad firmado. Se activó el acceso al portal del cliente y se enviaron las credenciales por correo.',
        ]);

        return $record->fresh();
    }
}
