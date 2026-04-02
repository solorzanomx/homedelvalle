<?php

namespace App\Services;

use App\Models\EasyBrokerSetting;
use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EasyBrokerService
{
    private ?EasyBrokerSetting $config = null;

    private function getConfig(): ?EasyBrokerSetting
    {
        if ($this->config === null) {
            $this->config = EasyBrokerSetting::first();
        }

        return $this->config;
    }

    private function getBaseUrl(): string
    {
        return rtrim($this->getConfig()?->base_url ?? 'https://api.easybroker.com/v1', '/');
    }

    private function getApiKey(): ?string
    {
        return $this->getConfig()?->api_key;
    }

    public function isConfigured(): bool
    {
        return $this->getConfig() !== null && !empty($this->getApiKey());
    }

    public function publish(Property $property): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'EasyBroker no esta configurado. Configura la API key en Administracion.'];
        }

        if ($property->hasEasyBrokerId()) {
            return $this->update($property);
        }

        $payload = $this->mapPropertyToPayload($property);

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/properties', $payload);

            Log::info('EasyBroker: POST /properties', [
                'property_id' => $property->id,
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $property->update([
                    'easybroker_id' => $data['public_id'] ?? null,
                    'easybroker_status' => 'published',
                    'easybroker_published_at' => now(),
                    'easybroker_public_url' => $data['public_url'] ?? null,
                ]);

                return ['success' => true, 'message' => 'Propiedad publicada en EasyBroker exitosamente.'];
            }

            Log::warning('EasyBroker: Error al publicar', [
                'property_id' => $property->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'message' => 'Error de EasyBroker: ' . ($response->json('error') ?? $response->body())];

        } catch (\Exception $e) {
            Log::error('EasyBroker: Excepcion al publicar', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error de conexion: ' . $e->getMessage()];
        }
    }

    public function update(Property $property): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'EasyBroker no esta configurado.'];
        }

        if (!$property->hasEasyBrokerId()) {
            return $this->publish($property);
        }

        $payload = $this->mapPropertyToPayload($property);

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
                'Content-Type' => 'application/json',
            ])->patch($this->getBaseUrl() . '/properties/' . $property->easybroker_id, $payload);

            Log::info('EasyBroker: PATCH /properties/' . $property->easybroker_id, [
                'property_id' => $property->id,
                'status_code' => $response->status(),
            ]);

            if ($response->successful()) {
                $property->update([
                    'easybroker_status' => 'published',
                    'easybroker_published_at' => now(),
                ]);

                return ['success' => true, 'message' => 'Propiedad actualizada en EasyBroker.'];
            }

            return ['success' => false, 'message' => 'Error: ' . ($response->json('error') ?? $response->body())];

        } catch (\Exception $e) {
            Log::error('EasyBroker: Excepcion al actualizar', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error de conexion: ' . $e->getMessage()];
        }
    }

    public function unpublish(Property $property): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'EasyBroker no esta configurado.'];
        }

        if (!$property->hasEasyBrokerId()) {
            return ['success' => false, 'message' => 'Esta propiedad no esta publicada en EasyBroker.'];
        }

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
                'Content-Type' => 'application/json',
            ])->patch($this->getBaseUrl() . '/properties/' . $property->easybroker_id, [
                'status' => 'not_published',
            ]);

            Log::info('EasyBroker: Unpublish /properties/' . $property->easybroker_id, [
                'property_id' => $property->id,
                'status_code' => $response->status(),
            ]);

            if ($response->successful()) {
                $property->update(['easybroker_status' => 'not_published']);

                return ['success' => true, 'message' => 'Propiedad despublicada de EasyBroker.'];
            }

            return ['success' => false, 'message' => 'Error: ' . ($response->json('error') ?? $response->body())];

        } catch (\Exception $e) {
            Log::error('EasyBroker: Excepcion al despublicar', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error de conexion: ' . $e->getMessage()];
        }
    }

    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'No hay API key configurada.'];
        }

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
            ])->get($this->getBaseUrl() . '/properties', ['limit' => 1]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Conexion exitosa con EasyBroker.'];
            }

            return ['success' => false, 'message' => 'Error: HTTP ' . $response->status() . ' - ' . $response->body()];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de conexion: ' . $e->getMessage()];
        }
    }

    private function mapPropertyToPayload(Property $property): array
    {
        $payload = [
            'title' => $property->title,
            'description' => $property->description ?? '',
            'property_type' => $property->property_type ?? 'House',
            'status' => 'published',
            'location' => [
                'name' => implode(', ', array_filter([
                    $property->colony,
                    $property->city,
                ])),
                'street' => $property->address,
                'postal_code' => $property->zipcode,
            ],
            'operations' => [
                [
                    'type' => $property->operation_type ?? 'sale',
                    'amount' => (float) $property->price,
                    'currency' => $property->currency ?? 'MXN',
                ],
            ],
        ];

        if ($property->bedrooms !== null) {
            $payload['bedrooms'] = $property->bedrooms;
        }
        if ($property->bathrooms !== null) {
            $payload['bathrooms'] = $property->bathrooms;
        }
        if ($property->parking !== null) {
            $payload['parking_spaces'] = $property->parking;
        }
        if ($property->area !== null) {
            $payload['construction_size'] = (float) $property->area;
        }

        return $payload;
    }
}
