<?php

namespace App\Support;

use App\Models\PolizaPlan;
use App\Models\PolizaRate;
use App\Models\PolizaTariffSheet;
use Illuminate\Support\Collection;

/**
 * Precio de una póliza jurídica según la RENTA MENSUAL, con la hoja de servicios vigente de Previsión Legal
 * (2026-09-27). Rangos por precio fijo; desde el último tope, un % de la renta mensual (p. ej. Integral 50%:
 * a $30,000 son exactamente los $15,000 del tramo anterior — así se validó la lectura de la hoja).
 *
 * Los gastos de emisión (hoy $1,700) se cubren al iniciar el trámite: SE ACREDITAN al precio si la operación se
 * concreta y no se reembolsan si no (decisión de Alejandro, 2026-09-27).
 */
class PolizaPricing
{
    public static function sheet(): ?PolizaTariffSheet
    {
        return PolizaTariffSheet::where('is_active', true)->latest('valid_year')->latest('id')->first();
    }

    /**
     * @return array{amount:float, basis:string, percent:?float, emission_fee:float, sheet_id:int, remaining_after_emission:float}|null
     *         null si no hay tarifa para esa renta/plan (renta vacía, plan sin tarifas…)
     */
    public static function quote(PolizaPlan $plan, float $monthlyRent, ?PolizaTariffSheet $sheet = null): ?array
    {
        $sheet ??= self::sheet();
        if (! $sheet || $monthlyRent <= 0) {
            return null;
        }

        $rate = PolizaRate::where('poliza_tariff_sheet_id', $sheet->id)
            ->where('poliza_plan_id', $plan->id)
            ->where('rent_over', '<', $monthlyRent)
            ->where(fn($q) => $q->whereNull('rent_up_to')->orWhere('rent_up_to', '>=', $monthlyRent))
            ->orderByDesc('rent_over')
            ->first();
        if (! $rate) {
            return null;
        }

        $percent = $rate->percent !== null ? (float) $rate->percent : null;
        $amount = $percent !== null ? round($monthlyRent * $percent / 100, 2) : (float) $rate->fixed_price;
        $fee = (float) $sheet->emission_fee;

        return [
            'amount' => $amount,
            'basis' => $percent !== null ? 'percent' : 'fixed',
            'percent' => $percent,
            'emission_fee' => $fee,
            'sheet_id' => $sheet->id,
            'remaining_after_emission' => max(0, round($amount - $fee, 2)),
        ];
    }

    /** Cotización de TODOS los planes ofrecidos para una renta: [plan_id => quote|null]. */
    public static function quotes(float $monthlyRent): Collection
    {
        $sheet = self::sheet();

        return PolizaPlan::offered()->get()->mapWithKeys(fn($p) => [$p->id => self::quote($p, $monthlyRent, $sheet)]);
    }

    /**
     * Reparto del costo: el inquilino paga $tenantShare % (100 o 50); el propietario el resto.
     *
     * @return array{tenant:float, owner:float, tenant_pct:int, owner_pct:int}
     */
    public static function split(float $amount, int $tenantShare): array
    {
        $tenantShare = max(0, min(100, $tenantShare));
        $tenant = round($amount * $tenantShare / 100, 2);

        return ['tenant' => $tenant, 'owner' => round($amount - $tenant, 2), 'tenant_pct' => $tenantShare, 'owner_pct' => 100 - $tenantShare];
    }

    /** Opciones de reparto que puede elegir el propietario (decisión de Alejandro: inquilino 100% o 50/50). */
    public const SHARE_OPTIONS = [100 => 'La paga el inquilino (100%)', 50 => 'Mitad y mitad (50% cada uno)'];
}
