<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Str;

class ClientPortalService
{
    // Interest types that trigger an automatic captación pipeline
    private const VENTA_TYPES = ['venta', 'venta_propietario', 'vendedor', 'propietario'];

    public function __construct(private CaptacionService $captacion) {}

    /**
     * Create a portal user account for a client.
     * Returns the created User and the plain-text password (if generated).
     * Also auto-activates the captación pipeline for venta clients.
     */
    public function createPortalAccount(Client $client, ?string $password = null): array
    {
        if ($client->user_id) {
            $this->maybeActivateCaptacion($client);
            return ['user' => User::find($client->user_id), 'password' => null];
        }

        // Si ya existe un usuario con ese email (huérfano de cliente borrado), reutilizarlo
        $existing = User::where('email', $client->email)->first();
        if ($existing) {
            $plain = $password ?: Str::random(10);
            $existing->update([
                'name'     => $client->name,
                'password' => $plain,
                'phone'    => $client->phone,
                'role'     => 'client',
            ]);
            $client->update(['user_id' => $existing->id]);
            $this->maybeActivateCaptacion($client);
            return ['user' => $existing, 'password' => $plain];
        }

        $plainPassword = $password ?: Str::random(10);

        $user = User::create([
            'name'     => $client->name,
            'email'    => $client->email,
            'password' => $plainPassword,
            'phone'    => $client->phone,
            'role'     => 'client',
        ]);

        $client->update(['user_id' => $user->id]);

        $this->maybeActivateCaptacion($client);

        return ['user' => $user, 'password' => $plainPassword];
    }

    /**
     * If the client has a venta interest type, ensure a captación pipeline exists.
     */
    private function maybeActivateCaptacion(Client $client): void
    {
        $types = $client->interest_types ?? [];
        if (empty($types) || !array_intersect(self::VENTA_TYPES, $types)) {
            return;
        }

        try {
            $this->captacion->getOrCreateForClient($client);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ClientPortalService: no se pudo crear captación', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the Client record linked to a portal user.
     */
    public function getClientForUser(User $user): ?Client
    {
        return Client::where('user_id', $user->id)->first();
    }

    /**
     * Get rental processes where client is owner or tenant.
     */
    public function getRentalsForClient(Client $client)
    {
        return \App\Models\RentalProcess::where('owner_client_id', $client->id)
            ->orWhere('tenant_client_id', $client->id)
            ->with(['property', 'ownerClient', 'tenantClient'])
            ->latest()
            ->get();
    }

    /**
     * Get documents related to a client's rental processes.
     */
    public function getDocumentsForClient(Client $client)
    {
        $rentalIds = \App\Models\RentalProcess::where('owner_client_id', $client->id)
            ->orWhere('tenant_client_id', $client->id)
            ->pluck('id');

        return \App\Models\Document::where('client_id', $client->id)
            ->orWhereIn('rental_process_id', $rentalIds)
            ->with(['rentalProcess', 'uploader'])
            ->latest()
            ->get();
    }
}
