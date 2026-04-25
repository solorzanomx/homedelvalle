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
        $errors = $this->validateConfig();
        if ($errors) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        if ($property->hasEasyBrokerId()) {
            return $this->update($property);
        }

        $payload = $this->mapPropertyToPayload($property);

        try {
            Log::error('EasyBroker: POST /properties payload', [
                'property_id' => $property->id,
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
                'Content-Type' => 'application/json',
            ])->post($this->getBaseUrl() . '/properties', $payload);

            Log::error('EasyBroker: POST /properties response', [
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

            return ['success' => false, 'message' => $this->parseApiError($response)];

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

            return ['success' => false, 'message' => $this->parseApiError($response)];

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

            return ['success' => false, 'message' => $this->parseApiError($response)];

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

    public function validateConfig(): array
    {
        $errors = [];
        if (!$this->isConfigured()) {
            $errors[] = 'Falta API Key. Configúrala en Administración → EasyBroker.';
        }
        $config = $this->getConfig();
        if (!$config?->default_latitude || !$config?->default_longitude) {
            $errors[] = 'Faltan coordenadas por defecto (latitud/longitud). Configúralas en Administración → EasyBroker.';
        }
        return $errors;
    }

    private function mapPropertyToPayload(Property $property): array
    {
        $config = $this->getConfig();

        $lat     = $property->latitude  ?? $config?->default_latitude;
        $lng     = $property->longitude ?? $config?->default_longitude;
        $cityId  = $config?->default_city_id;
        $adminId = $config?->default_admin_division_id;

        $opType = $property->operation_type ?? $config?->default_operation_type ?? 'sale';

        // city_id = municipality name, administrative_division_id = state name
        $resolvedCityId  = $cityId  ?: null;
        $resolvedAdminId = $adminId ?: null;

        $location = [];
        if ($property->address)   $location['street']      = $property->address;
        if ($property->zipcode)   $location['postal_code'] = $property->zipcode;
        if ($lat && $lng) {
            $location['latitude']  = (float) $lat;
            $location['longitude'] = (float) $lng;
        }

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

        $payload = [
            'title'         => $property->title,
            'status'        => 'published',
            'property_type' => $propertyType,
            'location'      => $location,
        ];

        if ($resolvedCityId)  $payload['city_id']                  = (int) $resolvedCityId;
        if ($resolvedAdminId) $payload['administrative_division_id'] = (int) $resolvedAdminId;

        $payload['operations'] = [
            [
                'type'     => $opType,
                'amount'   => (float) $property->price,
                'currency' => $property->currency ?? $config?->default_currency ?? 'MXN',
            ],
        ];

        $description = trim($property->description ?: $property->title);
        if ($description)                    $payload['description']       = $description;
        if ($property->bedrooms !== null)    $payload['bedrooms']          = (int) $property->bedrooms;
        if ($property->bathrooms !== null)   $payload['bathrooms']         = (float) $property->bathrooms;
        if ($property->parking !== null)     $payload['parking_spaces']    = (int) $property->parking;
        if ($property->area !== null)        $payload['construction_size'] = (float) $property->area;
        if ($property->lot_area !== null)    $payload['lot_size']          = (float) $property->lot_area;

        return $payload;
    }

    public function searchLocations(string $query): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => [], 'message' => 'API Key no configurada.'];
        }

        try {
            // EasyBroker uses 'search' not 'q'
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
            ])->get($this->getBaseUrl() . '/locations', ['search' => $query, 'limit' => 20]);

            if ($response->successful()) {
                $data = $response->json('content') ?? $response->json() ?? [];
                return ['success' => true, 'data' => is_array($data) ? $data : []];
            }

            Log::warning('EasyBroker: locations search failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return ['success' => false, 'data' => [], 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => [], 'message' => $e->getMessage()];
        }
    }

    public function detectLocationFromProperties(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'data' => null, 'message' => 'API Key no configurada.'];
        }

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
            ])->get($this->getBaseUrl() . '/properties', ['limit' => 5, 'page' => 1]);

            if (!$response->successful()) {
                return ['success' => false, 'data' => null, 'message' => $this->parseApiError($response)];
            }

            $props = $response->json('content') ?? [];
            foreach ($props as $prop) {
                $loc = $prop['location'] ?? [];
                if (!empty($loc['city_id']) || !empty($loc['administrative_division_id'])) {
                    return [
                        'success' => true,
                        'data'    => [
                            'city_id'    => $loc['city_id'] ?? null,
                            'admin_id'   => $loc['administrative_division_id'] ?? null,
                            'city_name'  => $loc['city'] ?? $loc['name'] ?? null,
                            'source'     => $prop['title'] ?? $prop['public_id'] ?? 'propiedad existente',
                        ],
                    ];
                }
            }

            return ['success' => false, 'data' => null, 'message' => 'No se encontraron propiedades con ubicación en EasyBroker.'];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => null, 'message' => $e->getMessage()];
        }
    }

    public function testPatch(string $publicId): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'API Key no configurada'];
        }

        $payload = [
            'title'         => 'Test desde HDV API - ' . now()->format('H:i:s'),
            'status'        => 'published',
            'property_type' => 'apartment',
            'location'      => [
                'street'       => 'amores',
                'neighborhood' => 'Del Valle Centro',
                'city_id'      => 9014,
                'administrative_division_id' => 9,
                'latitude'     => 19.3853297925,
                'longitude'    => -99.1660722852,
                'postal_code'  => '03100',
            ],
            'operations'    => [
                ['operation_type' => 'sale', 'amount' => 2795000, 'currency' => 'MXN'],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'X-Authorization' => $this->getApiKey(),
                'Content-Type'    => 'application/json',
            ])->patch($this->getBaseUrl() . '/properties/' . $publicId, $payload);

            return [
                'payload'     => $payload,
                'status_code' => $response->status(),
                'response'    => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function rawProperties(?string $publicId = null): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'API Key no configurada'];
        }

        try {
            if ($publicId) {
                $response = Http::withHeaders(['X-Authorization' => $this->getApiKey()])
                    ->get($this->getBaseUrl() . '/properties/' . $publicId);
            } else {
                $response = Http::withHeaders(['X-Authorization' => $this->getApiKey()])
                    ->get($this->getBaseUrl() . '/properties', ['limit' => 10, 'page' => 1]);
            }

            return $response->json() ?? ['error' => $response->body()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function rawLocationSearch(): array
    {
        if (!$this->isConfigured()) {
            return ['error' => 'API Key no configurada'];
        }

        $results = [];
        $attempts = [
            // Try navigating into CDMX to find municipalities with IDs
            ['url' => '/locations', 'params' => ['search' => 'Ciudad de México']],
            ['url' => '/locations', 'params' => ['search' => 'Benito Juárez']],
            ['url' => '/locations', 'params' => ['search' => 'Benito Juárez', 'type' => 'municipality']],
            ['url' => '/locations', 'params' => ['search' => 'Del Valle']],
            // Path-based navigation attempts
            ['url' => '/locations/Ciudad%20de%20M%C3%A9xico', 'params' => []],
            ['url' => '/locations/M%C3%A9xico/Ciudad%20de%20M%C3%A9xico', 'params' => []],
            ['url' => '/locations/Mexico/Benito-Juarez', 'params' => []],
            // Try with limit to get more results
            ['url' => '/locations', 'params' => ['limit' => 50]],
        ];

        foreach ($attempts as $attempt) {
            try {
                $response = Http::withHeaders(['X-Authorization' => $this->getApiKey()])
                    ->timeout(8)
                    ->get($this->getBaseUrl() . $attempt['url'], $attempt['params']);

                $body = $response->json() ?? $response->body();
                $results[] = [
                    'url'    => $attempt['url'],
                    'params' => $attempt['params'],
                    'status' => $response->status(),
                    'body'   => $body,
                ];
            } catch (\Exception $e) {
                $results[] = ['url' => $attempt['url'], 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    private function parseApiError(\Illuminate\Http\Client\Response $response): string
    {
        $json = $response->json();
        if (is_array($json)) {
            if (!empty($json['errors']) && is_array($json['errors'])) {
                return 'HTTP ' . $response->status() . ': ' . implode(', ', array_map(
                    fn($e) => is_array($e) ? ($e['message'] ?? json_encode($e)) : $e,
                    $json['errors']
                ));
            }
            if (!empty($json['error'])) return 'HTTP ' . $response->status() . ': ' . $json['error'];
            if (!empty($json['message'])) return 'HTTP ' . $response->status() . ': ' . $json['message'];
        }
        return 'HTTP ' . $response->status() . ': ' . $response->body();
    }
}
