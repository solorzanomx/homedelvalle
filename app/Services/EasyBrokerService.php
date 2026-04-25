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
        $config = $this->getConfig();

        $lat    = $property->latitude  ?? $config?->default_latitude;
        $lng    = $property->longitude ?? $config?->default_longitude;
        $cityId = $config?->default_city_id;
        $adminId = $config?->default_admin_division_id;

        $opType = $property->operation_type ?? $config?->default_operation_type ?? 'sale';

        $location = array_filter([
            'street'                     => $property->address  ?: null,
            'postal_code'                => $property->zipcode  ?: null,
            'city_id'                    => $cityId             ?: null,
            'administrative_division_id' => $adminId            ?: null,
            'latitude'                   => $lat  ? (float) $lat  : null,
            'longitude'                  => $lng  ? (float) $lng  : null,
        ]);

        // EasyBroker property_type must be lowercase
        $propertyTypeMap = [
            'House'      => 'house',
            'Apartment'  => 'apartment',
            'Land'       => 'land',
            'Office'     => 'office',
            'Commercial' => 'commercial',
            'Warehouse'  => 'warehouse',
            'Building'   => 'building',
        ];
        $propertyType = $propertyTypeMap[$property->property_type ?? ''] ?? 'house';

        $details = array_filter([
            'description'       => trim($property->description ?: $property->title),
            'bedrooms'          => $property->bedrooms   !== null ? (int) $property->bedrooms   : null,
            'bathrooms'         => $property->bathrooms  !== null ? (float) $property->bathrooms : null,
            'parking_spaces'    => $property->parking    !== null ? (int) $property->parking    : null,
            'construction_size' => $property->area       !== null ? (float) $property->area     : null,
            'lot_size'          => $property->lot_area   !== null ? (float) $property->lot_area  : null,
        ], fn($v) => $v !== null && $v !== '');

        return [
            'title'                   => $property->title,
            'status'                  => 'published',
            'property_type'           => $propertyType,
            'show_address_on_portals' => true,
            'location'                => $location,
            'operations'              => [
                [
                    'type'     => $opType,
                    'amount'   => (float) $property->price,
                    'currency' => $property->currency ?? $config?->default_currency ?? 'MXN',
                ],
            ],
            'details' => $details,
        ];
    }

    public function searchLocations(string $query): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => []];
        }

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
            ])->get($this->getBaseUrl() . '/locations', ['q' => $query, 'limit' => 20]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json('content') ?? $response->json() ?? []];
            }

            return ['success' => false, 'data' => []];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => []];
        }
    }
}
