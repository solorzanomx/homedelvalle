<?php

namespace App\Listeners;

use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Log;

/**
 * Crea cuenta de portal automáticamente cuando se firma una captación.
 * Activar poniendo config('portal.auto_create_accounts') = true en Portal-5.
 */
class CreatePortalAccountOnCaptacionSigned
{
    public function __construct(private readonly ClientPortalService $portal) {}

    public function handle(object $event): void
    {
        if (! config('portal.auto_create_accounts', false)) {
            return;
        }

        try {
            $client = $event->client ?? $event->captacion?->client ?? null;
            if (! $client) return;

            $result = $this->portal->createPortalAccount($client);

            if ($result['user'] && $result['password']) {
                $this->portal->sendWelcomeInvitation($result['user']);
            }
        } catch (\Throwable $e) {
            Log::warning('CreatePortalAccountOnCaptacionSigned: falló', ['error' => $e->getMessage()]);
        }
    }
}
