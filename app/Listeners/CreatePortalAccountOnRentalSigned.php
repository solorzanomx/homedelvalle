<?php

namespace App\Listeners;

use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Log;

/**
 * Crea cuenta de portal para propietario e inquilino al firmar contrato de renta.
 * Activar poniendo config('portal.auto_create_accounts') = true en Portal-5.
 */
class CreatePortalAccountOnRentalSigned
{
    public function __construct(private readonly ClientPortalService $portal) {}

    public function handle(object $event): void
    {
        if (! config('portal.auto_create_accounts', false)) {
            return;
        }

        try {
            $rental = $event->rental ?? $event->rentalProcess ?? null;
            if (! $rental) return;

            // Crear cuenta para propietario
            if ($rental->ownerClient) {
                $result = $this->portal->createPortalAccount($rental->ownerClient);
                if ($result['user'] && $result['password']) {
                    $this->portal->sendWelcomeInvitation($result['user']);
                }
            }

            // Crear cuenta para inquilino
            if ($rental->tenantClient) {
                $result = $this->portal->createPortalAccount($rental->tenantClient);
                if ($result['user'] && $result['password']) {
                    $this->portal->sendWelcomeInvitation($result['user']);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('CreatePortalAccountOnRentalSigned: falló', ['error' => $e->getMessage()]);
        }
    }
}
