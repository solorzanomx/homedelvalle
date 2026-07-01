<?php

namespace App\Services;

use App\Actions\Property\FetchStreetViewPhotoAction;
use App\Models\Captacion;
use App\Models\Client;
use App\Models\Operation;
use App\Models\OperationStageLog;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CaptacionIntakeService
{
    /**
     * Flujo completo post-llamada: Client → Property → Operation → Captacion.
     * Corre en una transacción. Si algo falla, no queda nada a medias.
     */
    public function createFromCall(array $data, User $agent): Captacion
    {
        $captacion = DB::transaction(function () use ($data, $agent) {
            $client    = $this->findOrCreateClient($data, $agent);
            $property  = $this->createProperty($data, $client, $agent);
            $operation = $this->createOperation($client, $property, $data, $agent);

            $captacion = Captacion::create([
                'client_id'           => $client->id,
                'property_id'         => $property->id,
                'operation_id'        => $operation->id,
                'property_address'    => $this->buildAddress($property),
                'portal_etapa'        => 1,
                'status'              => 'activo',
                'intent'              => $data['intent']         ?? 'general',
                'commission_pct'      => $data['commission_pct'] ?? 5.00,
                'marketing_plan'      => $data['marketing_plan'] ?? null,
                'notes_from_call'     => $data['notes_from_call'] ?? null,
                'source'              => $data['source']         ?? 'phone_call',
                'created_by_user_id'  => $agent->id,
            ]);

            // Vinculamos el operation a la captacion para trazabilidad
            $operation->update(['notes' => "Captación #{$captacion->id} — {$captacion->intent_label}"]);

            return $captacion;
        });

        // Intentar guardar foto de fachada (Street View) FUERA de la transacción
        // para no bloquearla durante la llamada HTTP a Google
        try {
            app(FetchStreetViewPhotoAction::class)->execute($captacion->property);
        } catch (\Throwable) {
            // Silencioso — no queremos romper el flujo de captación por esto
        }

        return $captacion;
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Busca cliente por email (si hay) o por teléfono. Si no existe, lo crea.
     * El agente queda como usuario asignado.
     */
    private function findOrCreateClient(array $data, User $agent): Client
    {
        $client = null;

        if (!empty($data['email'])) {
            $client = Client::where('email', $data['email'])->first();
        }

        if (!$client && !empty($data['phone'])) {
            $client = Client::where('phone', $data['phone'])
                            ->orWhere('whatsapp', $data['phone'])
                            ->first();
        }

        if ($client) {
            // Actualizar campos si el agente obtuvo más info en esta llamada
            $updates = array_filter([
                'email'    => empty($client->email)    ? ($data['email']    ?? null) : null,
                'whatsapp' => empty($client->whatsapp) ? ($data['whatsapp'] ?? null) : null,
            ]);
            if ($updates) {
                $client->update($updates);
            }
            return $client;
        }

        return Client::create([
            'name'             => $data['name'],
            'phone'            => $data['phone']    ?? null,
            'email'            => $data['email']    ?? null,
            'whatsapp'         => $data['whatsapp'] ?? $data['phone'] ?? null,
            'address'          => $data['client_address'] ?? null,
            'initial_notes'    => $data['notes_from_call'] ?? null,
            'client_type'      => 'owner',
            'lead_source'      => 'phone_call',
            'assigned_user_id' => $agent->id,
        ]);
    }

    /**
     * Crea la propiedad en status='inactive' (borrador hasta que se complete la captación).
     */
    private function createProperty(array $data, Client $client, User $agent): Property
    {
        return Property::create([
            'title'          => $this->buildPropertyTitle($data),
            'property_type'  => $data['property_type'],
            'operation_type' => $this->intentToOperationType($data['intent'] ?? 'general'),
            'colony'         => $data['colony'],
            'zipcode'        => $data['colony_cp'] ?? null,
            'city'           => $data['city']    ?? 'CDMX',
            'address'        => $data['address'] ?? null,
            'area'           => $data['area']    ?? null,
            'bedrooms'       => $data['bedrooms']   ?? null,
            'bathrooms'      => $data['bathrooms']  ?? null,
            'parking'        => $data['parking']    ?? null,
            'price'          => $data['price_expected'] ?? 0,
            'currency'       => 'MXN',
            'status'         => 'inactive',
            'client_id'      => $client->id,
        ]);
    }

    /**
     * Crea la Operation de captacion en stage='lead'.
     */
    private function createOperation(Client $client, Property $property, array $data, User $agent): Operation
    {
        $operation = Operation::create([
            'type'       => 'captacion',
            'stage'      => 'lead',
            'phase'      => 'captacion',
            'status'     => 'active',
            'property_id'=> $property->id,
            'client_id'  => $client->id,
            'user_id'    => $agent->id,
            'amount'     => $data['price_expected'] ?? null,
            'currency'   => 'MXN',
            'intent'     => $data['intent'] ?? 'general',
            'target_type'=> $this->intentToTargetType($data['intent'] ?? 'general'),
            'notes'      => $data['notes_from_call'] ?? null,
        ]);

        OperationStageLog::create([
            'operation_id' => $operation->id,
            'user_id'      => $agent->id,
            'from_stage'   => null,
            'to_stage'     => 'lead',
            'from_phase'   => null,
            'to_phase'     => 'captacion',
            'notes'        => 'Captación creada desde llamada · fuente: ' . ($data['source'] ?? 'phone_call'),
        ]);

        return $operation;
    }

    private function buildPropertyTitle(array $data): string
    {
        $type = match($data['property_type'] ?? '') {
            'House'      => 'Casa',
            'Apartment'  => 'Departamento',
            'Land'       => 'Terreno',
            'Office'     => 'Oficina',
            'Commercial' => 'Local comercial',
            'Warehouse'  => 'Bodega',
            'Building'   => 'Edificio',
            default      => $data['property_type'] ?? 'Inmueble',
        };

        return trim($type . ' · ' . ($data['colony'] ?? ''));
    }

    private function buildAddress(Property $property): string
    {
        return trim(
            ($property->colony ?? '') .
            ($property->address ? ', ' . $property->address : '') .
            ($property->city    ? ', ' . $property->city    : '')
        );
    }

    private function intentToOperationType(string $intent): string
    {
        return match($intent) {
            'renta_residencial', 'renta_comercial' => 'rental',
            default => 'sale',
        };
    }

    /**
     * Deriva el target_type de la Operation (venta/renta) para el auto-spawn
     * al completarse el pipeline de captación.
     */
    private function intentToTargetType(string $intent): string
    {
        return match($intent) {
            'renta_residencial', 'renta_comercial' => 'renta',
            default => 'venta',
        };
    }
}
