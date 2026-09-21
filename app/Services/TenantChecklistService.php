<?php

namespace App\Services;

use App\Mail\V4\Mailables\TenantChecklistInvitationMail;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * "Enviar checklist de requisitos para rentar" (2026-09-21) — crea el acceso
 * al portal si el inquilino todavía no lo tiene y le manda la lista de
 * documentos que se le piden, con el link a su portal (activación si es
 * cuenta nueva, login directo si ya existía). Punto de entrada tanto desde
 * la ficha del Cliente como desde la ficha del Lead (FormSubmissionController).
 */
class TenantChecklistService
{
    public function __construct(private ClientPortalService $portalService) {}

    public function send(Client $client): void
    {
        $isNewAccount = !$client->user_id;

        $result = $this->portalService->createPortalAccount($client);
        $user = $result['user'];

        $portalUrl = $isNewAccount
            ? rtrim(config('portal.url'), '/') . '/activar/' . $this->portalService->generateInvitationToken($user)
            : config('portal.url');

        try {
            Mail::to($client->email)->send(
                new TenantChecklistInvitationMail(
                    nombre: $client->name,
                    portalUrl: $portalUrl,
                    isNewAccount: $isNewAccount,
                )
            );
        } catch (\Exception $e) {
            Log::warning('TenantChecklistService: envío de checklist falló', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
