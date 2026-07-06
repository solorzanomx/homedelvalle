<?php

namespace App\Observers;

use App\Models\Client;
use Illuminate\Support\Facades\Log;

class ClientObserver
{
    /**
     * Mismo patrón que OperationObserver: rechaza un client_type fuera del
     * enum real, y lo autocompleta desde interest_types cuando el caller no
     * lo trae — para que ningún camino de alta nuevo repita el bug real
     * encontrado en la auditoría 2026-07-04 (valores como 'lead'/'tenant'
     * colándose fuera de Client::CLIENT_TYPES).
     */
    public function creating(Client $client): void
    {
        if ($client->client_type && !array_key_exists($client->client_type, Client::CLIENT_TYPES)) {
            throw new \InvalidArgumentException("Client::client_type invalido: '{$client->client_type}'.");
        }

        if (!$client->client_type && is_array($client->interest_types) && $client->interest_types) {
            $derived = Client::deriveClientType($client->interest_types);
            if ($derived) {
                Log::info('ClientObserver: client_type autocompletado desde interest_types al crear Client', [
                    'interest_types' => $client->interest_types,
                    'client_type'    => $derived,
                ]);
                $client->client_type = $derived;
            }
        }
    }
}
