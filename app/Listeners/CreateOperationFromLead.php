<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Models\Client;
use App\Models\Operation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Cuando llega un lead de renta (arrendatario o propietario_renta),
 * crea automáticamente el Client + Operation en el kanban correspondiente.
 *
 * arrendatario     → Operation(type='renta',     stage='lead')  → Colocación Activa
 * propietario_renta → Operation(type='captacion', stage='lead') → Captación de Renta
 */
class CreateOperationFromLead
{
    private const HANDLED_TYPES = ['arrendatario', 'propietario_renta'];

    public function handle(FormSubmitted $event): void
    {
        $sub = $event->submission;

        if (! in_array($sub->form_type, self::HANDLED_TYPES)) {
            return;
        }

        // Idempotencia — evitar duplicados si el evento se dispara dos veces
        $cacheKey = 'op_from_lead_' . $sub->id;
        if (! Cache::lock($cacheKey, 60)->get()) {
            return;
        }

        // ── 1. Buscar o crear el Client ────────────────────────────────────────
        $client = Client::where('email', $sub->email)->first();

        if (! $client) {
            $clientData = [
                'name'             => $sub->full_name,
                'email'            => $sub->email,
                'phone'            => $sub->phone,
                'whatsapp'         => $sub->phone,
                'client_type'      => $sub->client_type ?? 'lead',
                'lead_temperature' => $sub->lead_temperature ?? 'warm',
                'lead_source'      => 'web_form',
                'utm_source'       => $sub->utm_source,
                'utm_medium'       => $sub->utm_medium,
                'utm_campaign'     => $sub->utm_campaign,
                'initial_notes'    => $this->buildClientNotes($sub),
            ];

            // interest_types: array de strings
            if ($sub->form_type === 'arrendatario') {
                $clientData['interest_types'] = ['renta_inquilino'];
            } else {
                $clientData['interest_types'] = ['renta_propietario'];
            }

            $client = Client::create($clientData);
        }

        // ── 2. Crear la Operation ──────────────────────────────────────────────
        $payload = $sub->payload ?? [];

        if ($sub->form_type === 'arrendatario') {
            $this->createRentaOperation($sub, $client, $payload);
        } else {
            $this->createCaptacionOperation($sub, $client, $payload);
        }
    }

    // ── Arrendatario → Colocación Activa (type='renta') ───────────────────────

    private function createRentaOperation($sub, Client $client, array $payload): void
    {
        $rentaMap = [
            'hasta_15k'  => 15000,
            '15k_25k'    => 20000,
            '25k_40k'    => 32000,
            '40k_70k'    => 55000,
            '70k_plus'   => 80000,
        ];

        $rentaKey  = $payload['renta_mensual'] ?? null;
        $rentaMid  = $rentaKey ? ($rentaMap[$rentaKey] ?? null) : null;

        $notes = $this->buildOperationNotes($sub, $payload, 'arrendatario');

        Operation::create([
            'type'         => 'renta',
            'stage'        => 'lead',
            'status'       => 'active',
            'client_id'    => $client->id,
            'monthly_rent' => $rentaMid,
            'notes'        => $notes,
        ]);
    }

    // ── Propietario Renta → Captación (type='captacion') ─────────────────────

    private function createCaptacionOperation($sub, Client $client, array $payload): void
    {
        $rentaMap = [
            'hasta_15k'  => 15000,
            '15k_25k'    => 20000,
            '25k_40k'    => 32000,
            '40k_70k'    => 55000,
            '70k_plus'   => 80000,
            'no_se'      => null,
        ];

        $rentaKey  = $payload['renta_esperada'] ?? null;
        $rentaMid  = $rentaKey ? ($rentaMap[$rentaKey] ?? null) : null;

        $notes = $this->buildOperationNotes($sub, $payload, 'propietario_renta');

        $opData = [
            'type'         => 'captacion',
            'stage'        => 'lead',
            'status'       => 'active',
            'client_id'    => $client->id,
            'monthly_rent' => $rentaMid,
            'notes'        => $notes,
        ];

        // Agregar intent='renta' si la columna ya existe (Fase 1 schema)
        if (Schema::hasColumn('operations', 'intent')) {
            $opData['intent'] = 'renta';
        }

        Operation::create($opData);
    }

    // ── Helpers de notas ──────────────────────────────────────────────────────

    private function buildClientNotes($sub): string
    {
        $payload = $sub->payload ?? [];
        $lines   = ["Lead web — {$sub->form_type} · {$sub->created_at->format('d/m/Y H:i')}"];

        if (! empty($payload['colonia'])) {
            $lines[] = "Colonia: {$payload['colonia']}";
        }
        if (! empty($payload['zonas']) && is_array($payload['zonas'])) {
            $lines[] = "Zonas de interés: " . implode(', ', $payload['zonas']);
        }

        return implode("\n", $lines);
    }

    private function buildOperationNotes($sub, array $payload, string $tipo): string
    {
        $lines = ["Lead {$tipo} · Folio: " . ($sub->lead_tag ?? $sub->id)];

        // Arrendatario
        if (isset($payload['tipo_inmueble']) && is_array($payload['tipo_inmueble'])) {
            $lines[] = "Busca: " . implode(', ', $payload['tipo_inmueble']);
        }
        if (isset($payload['zonas']) && is_array($payload['zonas'])) {
            $lines[] = "Zonas: " . implode(', ', $payload['zonas']);
        }
        if (! empty($payload['recamaras'])) {
            $lines[] = "Recámaras: {$payload['recamaras']}";
        }
        if (! empty($payload['renta_mensual'])) {
            $lines[] = "Renta deseada: {$payload['renta_mensual']}";
        }
        if (! empty($payload['timing'])) {
            $lines[] = "Timing: {$payload['timing']}";
        }
        if (! empty($payload['garantia'])) {
            $lines[] = "Garantía: {$payload['garantia']}";
        }

        // Propietario
        if (! empty($payload['colonia'])) {
            $lines[] = "Colonia: {$payload['colonia']}";
        }
        if (! empty($payload['tipo_propiedad'])) {
            $lines[] = "Tipo: {$payload['tipo_propiedad']}";
        }
        if (! empty($payload['renta_esperada'])) {
            $lines[] = "Renta esperada: {$payload['renta_esperada']}";
        }
        if (! empty($payload['administracion'])) {
            $lines[] = "Administración: {$payload['administracion']}";
        }
        if (! empty($payload['poliza'])) {
            $lines[] = "Póliza: {$payload['poliza']}";
        }
        if (! empty($payload['must_have'])) {
            $lines[] = "Must-have: {$payload['must_have']}";
        }

        return implode("\n", $lines);
    }
}
