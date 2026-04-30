<?php

namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Models\Client;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Cuando llega un lead de renta crea Client + Operation en el kanban.
 *
 * arrendatario      → Operation(type='renta',     stage='lead')  → Colocación Activa
 * propietario_renta → Operation(type='captacion', stage='lead')  → Captación de Renta
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

        try {
            // ── 1. Obtener usuario admin de fallback ───────────────────────────
            $adminId = User::whereIn('role', ['admin', 'super_admin'])
                ->orderBy('id')
                ->value('id');

            if (! $adminId) {
                Log::warning('CreateOperationFromLead: no hay usuario admin para asignar.', ['submission_id' => $sub->id]);
                return;
            }

            // ── 2. Buscar o crear Client ───────────────────────────────────────
            $client = Client::firstOrCreate(
                ['email' => $sub->email],
                [
                    'name'             => $sub->full_name,
                    'phone'            => $sub->phone,
                    'whatsapp'         => $sub->phone,
                    'client_type'      => $sub->client_type ?? 'lead',
                    'lead_temperature' => $sub->lead_temperature ?? 'warm',
                    'lead_source'      => 'web_form',
                    'utm_source'       => $sub->utm_source,
                    'utm_medium'       => $sub->utm_medium,
                    'utm_campaign'     => $sub->utm_campaign,
                    'interest_types'   => $sub->form_type === 'arrendatario'
                                         ? ['renta_inquilino']
                                         : ['renta_propietario'],
                    'initial_notes'    => $this->buildClientNotes($sub),
                ]
            );

            $payload = $sub->payload ?? [];

            // ── 3. Evitar duplicado si ya existe Operation para este submission ─
            $alreadyExists = Operation::where('client_id', $client->id)
                ->where('notes', 'like', '%' . ($sub->lead_tag ?? $sub->id) . '%')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->exists();

            if ($alreadyExists) {
                return;
            }

            // ── 4. Crear Operation ─────────────────────────────────────────────
            if ($sub->form_type === 'arrendatario') {
                $this->createRentaOperation($sub, $client, $payload, $adminId);
            } else {
                $this->createCaptacionOperation($sub, $client, $payload, $adminId);
            }

            Log::info('CreateOperationFromLead: Operation creada.', [
                'submission_id' => $sub->id,
                'form_type'     => $sub->form_type,
                'client_id'     => $client->id,
            ]);

        } catch (\Throwable $e) {
            Log::error('CreateOperationFromLead: fallo al crear Operation.', [
                'submission_id' => $sub->id,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);
        }
    }

    // ── Arrendatario → Colocación Activa (type='renta') ───────────────────────

    private function createRentaOperation($sub, Client $client, array $payload, int $adminId): void
    {
        $rentaMap = [
            'hasta_15k' => 15000, '15k_25k' => 20000,
            '25k_40k'   => 32000, '40k_70k' => 55000, '70k_plus' => 80000,
        ];

        Operation::create([
            'type'         => 'renta',
            'phase'        => 'colocacion',
            'stage'        => 'lead',
            'status'       => 'active',
            'client_id'    => $client->id,
            'user_id'      => $adminId,
            'monthly_rent' => $rentaMap[$payload['renta_mensual'] ?? ''] ?? null,
            'notes'        => $this->buildNotes($sub, $payload),
        ]);
    }

    // ── Propietario Renta → Captación (type='captacion') ─────────────────────

    private function createCaptacionOperation($sub, Client $client, array $payload, int $adminId): void
    {
        $rentaMap = [
            'hasta_15k' => 15000, '15k_25k' => 20000,
            '25k_40k'   => 32000, '40k_70k' => 55000,
            '70k_plus'  => 80000, 'no_se'    => null,
        ];

        $opData = [
            'type'         => 'captacion',
            'phase'        => 'captacion',
            'stage'        => 'lead',
            'status'       => 'active',
            'client_id'    => $client->id,
            'user_id'      => $adminId,
            'monthly_rent' => $rentaMap[$payload['renta_esperada'] ?? ''] ?? null,
            'notes'        => $this->buildNotes($sub, $payload),
        ];

        // intent='renta' solo si la columna ya existe (Fase 1 schema)
        if (Schema::hasColumn('operations', 'intent')) {
            $opData['intent'] = 'renta';
        }

        Operation::create($opData);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildClientNotes($sub): string
    {
        $payload = $sub->payload ?? [];
        $lines   = ["Lead web — {$sub->form_type} · {$sub->created_at?->format('d/m/Y H:i')}"];
        if (! empty($payload['colonia']))                           $lines[] = "Colonia: {$payload['colonia']}";
        if (! empty($payload['zonas']) && is_array($payload['zonas'])) $lines[] = "Zonas: " . implode(', ', $payload['zonas']);
        return implode("\n", $lines);
    }

    private function buildNotes($sub, array $payload): string
    {
        $folio = $sub->lead_tag ?? ('SUB-' . $sub->id);
        $lines = ["Folio: {$folio}"];

        // Arrendatario
        if (! empty($payload['tipo_inmueble']) && is_array($payload['tipo_inmueble']))
            $lines[] = "Busca: " . implode(', ', $payload['tipo_inmueble']);
        if (! empty($payload['zonas']) && is_array($payload['zonas']))
            $lines[] = "Zonas: " . implode(', ', $payload['zonas']);
        if (! empty($payload['recamaras']))     $lines[] = "Recámaras: {$payload['recamaras']}";
        if (! empty($payload['renta_mensual'])) $lines[] = "Renta deseada: {$payload['renta_mensual']}";
        if (! empty($payload['timing']))        $lines[] = "Timing: {$payload['timing']}";
        if (! empty($payload['garantia']))      $lines[] = "Garantía: {$payload['garantia']}";
        if (! empty($payload['mascotas']))      $lines[] = "Mascotas: {$payload['mascotas']}";
        if (! empty($payload['must_have']))     $lines[] = "Must-have: {$payload['must_have']}";

        // Propietario
        if (! empty($payload['colonia']))         $lines[] = "Colonia: {$payload['colonia']}";
        if (! empty($payload['tipo_propiedad']))  $lines[] = "Tipo: {$payload['tipo_propiedad']}";
        if (! empty($payload['renta_esperada']))  $lines[] = "Renta esperada: {$payload['renta_esperada']}";
        if (! empty($payload['administracion']))  $lines[] = "Administración: {$payload['administracion']}";
        if (! empty($payload['poliza']))          $lines[] = "Póliza: {$payload['poliza']}";
        if (! empty($payload['estado_doc']))      $lines[] = "Docs: {$payload['estado_doc']}";

        return implode("\n", $lines);
    }
}
