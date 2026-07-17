<?php

namespace App\Services;

use App\Models\EasyBrokerSetting;
use App\Models\Property;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integración con la API pública de EasyBroker (dev.easybroker.com).
 *
 * Reescrito 2026-07-16 contra la referencia oficial, verificado contra el
 * ambiente de pruebas (api.stagingeb.com). Lo que la versión anterior hacía
 * mal (sesión de debugging 2026-04-25): mandaba city_id /
 * administrative_division_id (no existen en la API), location como bolsa de
 * campos sueltos, property_type en inglés minúsculas, y buscaba ubicaciones
 * con `search` (el parámetro real es `query`).
 *
 * Formato correcto (POST /properties, endpoint marcado Beta):
 * - property_type: nombre EXACTO del catálogo /property_types (español:
 *   "Casa", "Departamento", "Casa con uso de suelo", "Terreno"…).
 * - location: OBJETO { name (full_name del catálogo /locations, ej.
 *   "Del Valle Norte, Benito Juárez, Ciudad de México"), street, latitude,
 *   longitude, postal_code, show_exact_location }.
 * - operations: [{ type: sale|rental, amount, currency }].
 *
 * Nota Beta: en staging el POST con el payload documentado responde
 * {"errors":{"operation_type":["no ha sido seleccionado"]}} — si producción
 * repite ese error, es ticket a soporte de EasyBroker (la doc lo pide
 * explícitamente para el endpoint Beta).
 */
class EasyBrokerService
{
    private ?EasyBrokerSetting $config = null;

    /** Interno (inglés, BD local) → catálogo EasyBroker (español, exacto). */
    private const PROPERTY_TYPE_MAP = [
        'House'      => 'Casa',
        'Apartment'  => 'Departamento',
        'Land'       => 'Terreno',
        'Office'     => 'Oficina',
        'Commercial' => 'Local comercial',
        'Warehouse'  => 'Bodega comercial',
        'Building'   => 'Edificio',
    ];

    private const DEFAULT_LOCATION = 'Benito Juárez, Ciudad de México';

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

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'X-Authorization' => $this->getApiKey(),
            'Content-Type'    => 'application/json',
        ])->timeout(15);
    }

    public function isConfigured(): bool
    {
        return $this->getConfig() !== null && ! empty($this->getApiKey());
    }

    public function validateConfig(): array
    {
        $errors = [];
        if (! $this->isConfigured()) {
            $errors[] = 'Falta API Key. Configúrala en Administración → EasyBroker.';
        }

        return $errors;
    }

    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'No hay API key configurada.'];
        }

        try {
            $response = $this->http()->get($this->getBaseUrl() . '/properties', ['limit' => 1]);

            if ($response->successful()) {
                $total = $response->json('pagination.total');

                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con EasyBroker.'
                        . ($total !== null ? " Tu cuenta tiene {$total} propiedades." : ''),
                ];
            }

            return ['success' => false, 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    /**
     * GET /locations — jerárquico: sin query devuelve el país y sus estados;
     * con query (el full_name de una ubicación) devuelve esa ubicación y sus
     * hijos. Ej.: "Benito Juárez, Ciudad de México" → sus colonias.
     */
    public function searchLocations(string $query): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'data' => [], 'message' => 'API Key no configurada.'];
        }

        try {
            $response = $this->http()->get($this->getBaseUrl() . '/locations', ['query' => $query]);

            if ($response->successful()) {
                $data = $response->json() ?? [];

                return ['success' => true, 'data' => is_array($data) ? $data : []];
            }

            return ['success' => false, 'data' => [], 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => [], 'message' => $e->getMessage()];
        }
    }

    /**
     * Propiedades PUBLICADAS de la cuenta (la lista completa incluye todo el
     * histórico: vendidas, suspendidas, borradores…).
     */
    public function publishedProperties(int $page = 1, int $limit = 50): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'data' => [], 'pagination' => null, 'message' => 'API Key no configurada.'];
        }

        try {
            $response = $this->http()->get($this->getBaseUrl() . '/properties', [
                'page'  => $page,
                'limit' => $limit,
                'search[statuses][]' => 'published',
            ]);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'data'       => $response->json('content') ?? [],
                    'pagination' => $response->json('pagination'),
                ];
            }

            return ['success' => false, 'data' => [], 'pagination' => null, 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => [], 'pagination' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Solicitudes de contacto (leads que preguntan por propiedades en
     * EasyBroker y sus portales vinculados). Vienen de más reciente a más
     * antigua. Shape: id, name, phone, email, contact_id, property_id,
     * message, source, happened_at.
     */
    public function contactRequests(int $page = 1, int $limit = 50): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'data' => [], 'pagination' => null, 'message' => 'API Key no configurada.'];
        }

        try {
            $response = $this->http()->get($this->getBaseUrl() . '/contact_requests', [
                'page'  => $page,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'data'       => $response->json('content') ?? [],
                    'pagination' => $response->json('pagination'),
                ];
            }

            return ['success' => false, 'data' => [], 'pagination' => null, 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => [], 'pagination' => null, 'message' => $e->getMessage()];
        }
    }

    /** Nombres del catálogo /property_types (cache 1 h). */
    public function propertyTypes(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        return Cache::remember('easybroker.property_types', 3600, function () {
            $response = $this->http()->get($this->getBaseUrl() . '/property_types');

            return $response->successful()
                ? array_column($response->json('content') ?? [], 'name')
                : [];
        });
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
            Log::info('EasyBroker: POST /properties payload', [
                'property_id' => $property->id,
                'payload'     => $payload,
            ]);

            $response = $this->http()->post($this->getBaseUrl() . '/properties', $payload);

            Log::info('EasyBroker: POST /properties response', [
                'property_id' => $property->id,
                'status_code' => $response->status(),
                'response'    => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $property->update([
                    'easybroker_id'           => $data['public_id'] ?? null,
                    'easybroker_status'       => 'not_published',
                    'easybroker_published_at' => now(),
                    'easybroker_public_url'   => $data['public_url'] ?? null,
                ]);

                // Se crea como borrador a propósito: la doc advierte que una
                // propiedad creada como published se manda de inmediato a
                // todos los portales vinculados.
                return [
                    'success' => true,
                    'message' => 'Propiedad creada en EasyBroker como borrador (not_published). Revísala y publícala desde EasyBroker.',
                ];
            }

            return ['success' => false, 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            Log::error('EasyBroker: Excepción al publicar', [
                'property_id' => $property->id,
                'error'       => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function update(Property $property): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'EasyBroker no está configurado.'];
        }

        if (! $property->hasEasyBrokerId()) {
            return $this->publish($property);
        }

        // Sin 'status': el estado de publicación se administra en EasyBroker
        // y un PATCH no debe re-publicar ni despublicar por accidente.
        $payload = $this->mapPropertyToPayload($property, includeStatus: false);

        try {
            $response = $this->http()->patch(
                $this->getBaseUrl() . '/properties/' . $property->easybroker_id,
                $payload
            );

            Log::info('EasyBroker: PATCH /properties/' . $property->easybroker_id, [
                'property_id' => $property->id,
                'status_code' => $response->status(),
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Propiedad actualizada en EasyBroker.'];
            }

            return ['success' => false, 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            Log::error('EasyBroker: Excepción al actualizar', [
                'property_id' => $property->id,
                'error'       => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function unpublish(Property $property): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'EasyBroker no está configurado.'];
        }

        if (! $property->hasEasyBrokerId()) {
            return ['success' => false, 'message' => 'Esta propiedad no está publicada en EasyBroker.'];
        }

        try {
            $response = $this->http()->patch(
                $this->getBaseUrl() . '/properties/' . $property->easybroker_id,
                ['status' => 'not_published']
            );

            if ($response->successful()) {
                $property->update(['easybroker_status' => 'not_published']);

                return ['success' => true, 'message' => 'Propiedad despublicada de EasyBroker.'];
            }

            return ['success' => false, 'message' => $this->parseApiError($response)];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
        }
    }

    public function detectLocationFromProperties(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'data' => null, 'message' => 'API Key no configurada.'];
        }

        try {
            $response = $this->http()->get($this->getBaseUrl() . '/properties', ['limit' => 5, 'page' => 1]);

            if (! $response->successful()) {
                return ['success' => false, 'data' => null, 'message' => $this->parseApiError($response)];
            }

            foreach (($response->json('content') ?? []) as $prop) {
                $loc = $prop['location'] ?? null;
                if (is_string($loc) && $loc !== '') {
                    return [
                        'success' => true,
                        'data'    => ['location_name' => $loc, 'source' => $prop['title'] ?? $prop['public_id'] ?? 'propiedad existente'],
                    ];
                }
                if (is_array($loc) && ! empty($loc['name'])) {
                    return [
                        'success' => true,
                        'data'    => ['location_name' => $loc['name'], 'source' => $prop['title'] ?? $prop['public_id'] ?? 'propiedad existente'],
                    ];
                }
            }

            return ['success' => false, 'data' => null, 'message' => 'No se encontraron propiedades con ubicación en EasyBroker.'];
        } catch (\Exception $e) {
            return ['success' => false, 'data' => null, 'message' => $e->getMessage()];
        }
    }

    /**
     * Resuelve la colonia local al full_name del catálogo de EasyBroker.
     * Consulta las colonias de Benito Juárez una vez (cache 1 h) y hace match
     * insensible a mayúsculas/acentos. Fallback: la alcaldía completa.
     */
    private function resolveLocationName(Property $property): string
    {
        $default = $this->getConfig()?->default_location_name ?: self::DEFAULT_LOCATION;

        $colonia = $property->marketColonia?->name ?: $property->colony;
        if (! $colonia) {
            return $default;
        }

        $localities = Cache::remember('easybroker.bj_localities', 3600, function () {
            $result = $this->searchLocations(self::DEFAULT_LOCATION);

            return $result['success'] ? ($result['data']['localities'] ?? []) : [];
        });

        $wanted = $this->normalize($colonia);
        foreach ($localities as $loc) {
            if ($this->normalize($loc['name'] ?? '') === $wanted) {
                return $loc['full_name'];
            }
        }
        // Segundo intento: contiene (ej. "Del Valle" → "Del Valle Centro")
        foreach ($localities as $loc) {
            if (str_contains($this->normalize($loc['name'] ?? ''), $wanted)) {
                return $loc['full_name'];
            }
        }

        return $default;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }

    private function mapPropertyToPayload(Property $property, bool $includeStatus = true): array
    {
        $config = $this->getConfig();

        $lat = $property->latitude  ?? $config?->default_latitude;
        $lng = $property->longitude ?? $config?->default_longitude;

        $location = [
            'name'                => $this->resolveLocationName($property),
            'show_exact_location' => false,
        ];
        if ($property->address) $location['street']      = $property->address;
        if ($property->zipcode) $location['postal_code'] = $property->zipcode;
        if ($lat && $lng) {
            $location['latitude']  = (float) $lat;
            $location['longitude'] = (float) $lng;
        }

        $type = $property->operation_type === 'rental' ? 'rental' : 'sale';

        $payload = [
            'title'         => $property->title,
            'description'   => trim($property->description ?: $property->title),
            'property_type' => self::PROPERTY_TYPE_MAP[$property->property_type ?? ''] ?? 'Casa',
            'location'      => $location,
            'operations'    => [[
                'type'     => $type,
                'amount'   => (float) $property->price,
                'currency' => $property->currency ?? $config?->default_currency ?? 'MXN',
            ]],
            'internal_id'   => 'HDV-' . $property->id,
        ];

        if ($includeStatus) {
            $payload['status'] = 'not_published';
        }

        if ($property->bedrooms !== null)          $payload['bedrooms']          = (int) $property->bedrooms;
        if ($property->bathrooms !== null)         $payload['bathrooms']         = (int) $property->bathrooms;
        if ($property->half_bathrooms !== null)    $payload['half_bathrooms']    = (int) $property->half_bathrooms;
        if ($property->parking !== null)           $payload['parking_spaces']    = (int) $property->parking;
        if ($property->construction_area ?? $property->area) {
            $payload['construction_size'] = (float) ($property->construction_area ?? $property->area);
        }
        if ($property->lot_area !== null)          $payload['lot_size']          = (float) $property->lot_area;

        return $payload;
    }

    private function parseApiError(\Illuminate\Http\Client\Response $response): string
    {
        $json = $response->json();
        if (is_array($json)) {
            // Formato {"errors": {"campo": ["mensaje", ...]}}
            if (! empty($json['errors']) && is_array($json['errors'])) {
                $parts = [];
                foreach ($json['errors'] as $field => $messages) {
                    $messages = is_array($messages) ? implode(', ', array_map(
                        fn ($m) => is_array($m) ? ($m['message'] ?? json_encode($m)) : $m,
                        $messages
                    )) : $messages;
                    $parts[] = is_int($field) ? $messages : "{$field}: {$messages}";
                }

                return 'HTTP ' . $response->status() . ': ' . implode(' | ', $parts);
            }
            if (! empty($json['error']))   return 'HTTP ' . $response->status() . ': ' . $json['error'];
            if (! empty($json['message'])) return 'HTTP ' . $response->status() . ': ' . $json['message'];
        }

        return 'HTTP ' . $response->status() . ': ' . $response->body();
    }
}
