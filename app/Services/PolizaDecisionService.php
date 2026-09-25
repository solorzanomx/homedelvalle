<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PolizaJuridica;
use App\Models\PolizaPlan;
use App\Models\RentalProcess;
use App\Support\PolizaPricing;
use App\Support\TenantRoadmap;
use Illuminate\Support\Facades\Log;

/**
 * El PROPIETARIO decide qué póliza jurídica se contrata y cómo se reparte el costo (inquilino 100% o 50/50);
 * después el inquilino solo lo ve (2026-09-27). El asesor puede decidir a nombre del dueño ('advisor').
 * Congela una "foto" del precio (monto, gastos de emisión, hoja) para que un cambio de tarifario no altere lo ya acordado.
 */
class PolizaDecisionService
{
    /** ¿Puede decidirse ahora? null = sí; texto = por qué no. */
    public function blocker(RentalProcess $rental): ?string
    {
        if (TenantRoadmap::route($rental) !== TenantRoadmap::ROUTE_POLIZA) {
            return 'La garantía de este inquilino no es una póliza jurídica.';
        }
        if ((float) $rental->monthly_rent <= 0) {
            return 'Falta la renta mensual del trato: sin ella no se puede calcular la tarifa.';
        }
        if ($rental->poliza && $rental->poliza->status === 'approved') {
            return 'La póliza ya fue aprobada; para cambiarla habla con tu asesor.';
        }

        return null;
    }

    /**
     * @param  string  $by  'owner' | 'advisor'
     * @return array{amount:float, split:array}
     */
    public function decide(RentalProcess $rental, PolizaPlan $plan, int $tenantShare, string $by, ?int $userId = null): array
    {
        if ($reason = $this->blocker($rental)) {
            throw new \DomainException($reason);
        }
        if (! array_key_exists($tenantShare, PolizaPricing::SHARE_OPTIONS)) {
            throw new \DomainException('El reparto debe ser 100% inquilino o 50/50.');
        }
        $quote = PolizaPricing::quote($plan, (float) $rental->monthly_rent);
        if (! $quote) {
            throw new \DomainException('No hay tarifa para ese plan con esa renta.');
        }

        $rental->update([
            'guarantee_type' => 'poliza_juridica',
            'poliza_plan_id' => $plan->id,
            'poliza_plan_selected_at' => now(),
            'poliza_tenant_share' => $tenantShare,
            'poliza_decided_by' => $by,
            'poliza_quote_amount' => $quote['amount'],
            'poliza_emission_fee' => $quote['emission_fee'],
            'poliza_tariff_sheet_id' => $quote['sheet_id'],
        ]);

        // Registro de la póliza para que el asesor tramite el alta (si ya existe, solo se actualiza el costo).
        $existing = PolizaJuridica::where('rental_process_id', $rental->id)->first();
        PolizaJuridica::updateOrCreate(
            ['rental_process_id' => $rental->id],
            ['tenant_client_id' => $rental->tenant_client_id, 'insurance_company' => $plan->provider_name, 'cost' => $quote['amount'], 'currency' => $plan->currency ?? 'MXN']
                + ($existing ? [] : ['status' => 'pending'])
        );

        $split = PolizaPricing::split($quote['amount'], $tenantShare);
        $this->notifyAdvisor($rental, $plan, $quote['amount'], $split, $by);

        return ['amount' => $quote['amount'], 'split' => $split];
    }

    private function notifyAdvisor(RentalProcess $rental, PolizaPlan $plan, float $amount, array $split, string $by): void
    {
        $userId = $rental->broker_id ?? $rental->user_id;
        if (! $userId) {
            return;
        }
        $who = $by === 'owner' ? 'El propietario' : 'Un asesor';
        $reparto = $split['tenant_pct'] === 100 ? 'la paga el inquilino (100%)' : 'mitad y mitad ($' . number_format($split['tenant']) . ' cada uno)';
        Notification::create([
            'user_id' => $userId,
            'type' => 'poliza_decidida',
            'title' => 'Póliza elegida',
            'body' => "{$who} eligió el plan {$plan->name} ($" . number_format($amount) . ") para la renta #{$rental->id}; {$reparto}. Tramita el alta con {$plan->provider_name}.",
            'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
        ]);
    }

    /** Avisa al propietario (portal + correo) que su inquilino no tiene aval y le toca elegir la póliza. Una sola vez por renta. */
    public function askOwnerToDecide(RentalProcess $rental, bool $force = false): void
    {
        $owner = $rental->ownerClient;
        if (! $owner || $rental->poliza_plan_id) {
            return;
        }
        if (! $force && Notification::where('type', 'poliza_elegir_propietario')->where('data->rental_id', $rental->id)->exists()) {
            return;
        }

        if ($owner->user_id) {
            Notification::create([
                'user_id' => $owner->user_id,
                'type' => 'poliza_elegir_propietario',
                'title' => 'Elige la póliza de tu inquilino',
                'body' => 'Tu inquilino no tiene aval en CDMX, así que la garantía es una póliza jurídica. Elige el plan y cómo se reparte el costo.',
                'data' => ['url' => route('portal.rentals.show', $rental->id), 'rental_id' => $rental->id],
            ]);
        }
        if ($owner->email) {
            try {
                app(EmailService::class)->send(
                    $owner->email,
                    'Elige la póliza jurídica de tu inquilino',
                    '<div style="font-family:Arial,sans-serif;font-size:15px;color:#0f172a;line-height:1.5;max-width:560px;">'
                    . '<p>Hola ' . e(explode(' ', trim($owner->name))[0]) . ',</p>'
                    . '<p>Tu inquilino no cuenta con un aval con propiedad en CDMX, así que su garantía será una <strong>póliza jurídica</strong> de Previsión Legal.</p>'
                    . '<p>Entra a tu Portal para <strong>elegir el plan</strong> (verás el precio según tu renta y qué cubre cada uno) y decidir <strong>cómo se reparte el costo</strong>: el inquilino al 100% o mitad y mitad.</p>'
                    . '<p style="margin:24px 0;"><a href="' . e(route('portal.rentals.show', $rental->id)) . '" style="background:#1D4ED8;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;display:inline-block;">Elegir la póliza</a></p>'
                    . '<p>Saludos,<br>Home del Valle Bienes Raíces</p></div>',
                    $owner->name,
                );
            } catch (\Throwable $e) {
                Log::warning('PolizaDecisionService: no se pudo enviar el correo al propietario', ['rental_id' => $rental->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
