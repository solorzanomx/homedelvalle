<?php

namespace App\Services;

use App\Models\MarketColonia;
use App\Models\MarketZone;
use App\Models\MarketZoneSnapshot;

class QuickQuoteService
{
    /**
     * Calcula los 4 escenarios de valor para un inmueble.
     *
     * @param  int    $coloniaId         market_colonia_id
     * @param  string $propertyType      apartment | house | office | land
     * @param  float  $m2Construction    m² de construcción
     * @param  float  $m2Land            m² de terreno (0 = desconocido)
     * @param  string $ageCategory       new | mid | old
     * @return array
     */
    public function calculate(
        int    $coloniaId,
        string $propertyType,
        float  $m2Construction,
        float  $m2Land = 0,
        string $ageCategory = 'mid',
    ): array {
        $colonia = MarketColonia::find($coloniaId);
        $zone    = $colonia?->zone;

        if (! $zone || $m2Construction < 10) {
            return ['available' => false, 'reason' => 'Datos insuficientes'];
        }

        // ── Obtener snapshots de mercado ──────────────────────────────
        $saleAptSnap  = $this->snap($zone->id, 'sale', 'apartment', $ageCategory);
        $saleHouseSnap= $this->snap($zone->id, 'sale', 'house', $ageCategory);
        $rentAptSnap  = $this->snap($zone->id, 'rent', 'apartment', $ageCategory);
        $rentOfficeSnap = $this->snap($zone->id, 'rent', 'office', 'mid'); // oficinas → seminuevo como referencia

        // Snap de venta según tipo
        $saleSnap = match($propertyType) {
            'house'  => $saleHouseSnap ?? $saleAptSnap,
            'office' => $saleAptSnap,  // usamos apt como proxy comercial
            default  => $saleAptSnap ?? $saleHouseSnap,
        };

        $rentResSnap = $rentAptSnap; // habitacional siempre departamento como referencia

        // ── Escenario 1: Venta como vivienda ──────────────────────────
        $saleResidential = null;
        if ($saleSnap && in_array($propertyType, ['apartment', 'house'])) {
            $saleResidential = $this->priceRange(
                (float) $saleSnap->price_m2_low,
                (float) $saleSnap->price_m2_avg,
                (float) $saleSnap->price_m2_high,
                $m2Construction,
                $saleSnap->confidence,
                $saleSnap->sample_size,
            );
        }

        // ── Escenario 2: Renta habitacional ───────────────────────────
        $rentResidential = null;
        if ($rentResSnap && in_array($propertyType, ['apartment', 'house'])) {
            $rentResidential = $this->priceRange(
                (float) $rentResSnap->price_m2_low,
                (float) $rentResSnap->price_m2_avg,
                (float) $rentResSnap->price_m2_high,
                $m2Construction,
                $rentResSnap->confidence,
                $rentResSnap->sample_size,
                mode: 'monthly',
            );
        }

        // ── Escenario 3: Renta comercial / oficina ────────────────────
        $rentCommercial = null;
        if ($rentOfficeSnap) {
            $rentCommercial = $this->priceRange(
                (float) $rentOfficeSnap->price_m2_low,
                (float) $rentOfficeSnap->price_m2_avg,
                (float) $rentOfficeSnap->price_m2_high,
                $m2Construction,
                $rentOfficeSnap->confidence,
                $rentOfficeSnap->sample_size,
                mode: 'monthly',
            );
        }

        // ── Escenario 4: Venta a constructor ──────────────────────────
        // Constructor paga por potencial de suelo, no por construcción existente.
        // Factor según antigüedad: mayor antigüedad → más cercano al valor puro de suelo.
        $constructorFactor = match($ageCategory) {
            'new' => 0.62,   // demoler lo nuevo no tiene sentido → menor valor
            'mid' => 0.72,
            'old' => 0.83,   // antiguo → casi valor suelo puro
            default => 0.72,
        };

        $saleConstructor = null;
        if ($saleSnap) {
            if ($m2Land > 0) {
                // Precio de suelo estimado: precio_venta_m2 × m2_terreno × factor_suelo
                // En BJ el suelo representa ~55-65% del valor final
                $landFactor = 0.60;
                $landPriceM2Low  = (float) $saleSnap->price_m2_low  * $m2Construction / max($m2Land, 1) * $landFactor;
                $landPriceM2Avg  = (float) $saleSnap->price_m2_avg  * $m2Construction / max($m2Land, 1) * $landFactor;
                $landPriceM2High = (float) $saleSnap->price_m2_high * $m2Construction / max($m2Land, 1) * $landFactor;

                $saleConstructor = $this->priceRange(
                    $landPriceM2Low, $landPriceM2Avg, $landPriceM2High,
                    $m2Land,
                    $saleSnap->confidence,
                    $saleSnap->sample_size,
                );
                $saleConstructor['note'] = "Basado en {$m2Land} m² de terreno · Valor potencial de suelo";
            } else {
                // Sin terreno: estimar como % del valor vivienda
                $totalLow  = (int) round((float) $saleSnap->price_m2_low  * $m2Construction * $constructorFactor / 50000) * 50000;
                $totalMid  = (int) round((float) $saleSnap->price_m2_avg  * $m2Construction * $constructorFactor / 50000) * 50000;
                $totalHigh = (int) round((float) $saleSnap->price_m2_high * $m2Construction * $constructorFactor / 50000) * 50000;

                $saleConstructor = [
                    'low'        => $totalLow,
                    'mid'        => $totalMid,
                    'high'       => $totalHigh,
                    'confidence' => $saleSnap->confidence,
                    'samples'    => $saleSnap->sample_size,
                    'note'       => 'Estimado: ' . round($constructorFactor * 100) . '% del valor vivienda · Para mayor precisión ingresa m² de terreno',
                ];
            }
        }

        // ── Resultado ─────────────────────────────────────────────────
        return [
            'available'        => true,
            'zone_name'        => $zone->name,
            'colonia_name'     => $colonia->name,
            'period'           => $saleSnap?->period?->translatedFormat('F Y'),
            'property_type'    => $propertyType,
            'm2_construction'  => $m2Construction,
            'm2_land'          => $m2Land,
            'age_category'     => $ageCategory,
            'sale_residential' => $saleResidential,
            'rent_residential' => $rentResidential,
            'rent_commercial'  => $rentCommercial,
            'sale_constructor' => $saleConstructor,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function snap(int $zoneId, string $op, string $type, string $age): ?MarketZoneSnapshot
    {
        // Busca exact match, luego fallback sin edad
        return MarketZoneSnapshot::where('market_zone_id', $zoneId)
            ->where('operation_type', $op)
            ->where('property_type', $type)
            ->where('age_category', $age)
            ->orderByDesc('period')
            ->first()
            ?? MarketZoneSnapshot::where('market_zone_id', $zoneId)
                ->where('operation_type', $op)
                ->where('property_type', $type)
                ->orderByDesc('period')
                ->first();
    }

    /**
     * Calcula rangos de precio con spread mínimo del 20%.
     * mode: 'total' → precio total; 'monthly' → renta mensual
     */
    private function priceRange(
        float  $m2Low,
        float  $m2Avg,
        float  $m2High,
        float  $m2,
        string $confidence,
        int    $samples,
        string $mode = 'total',
    ): array {
        // Garantizar spread mínimo del 20%
        if ($m2Avg > 0 && ($m2High - $m2Low) / $m2Avg < 0.20) {
            $m2Low  = $m2Avg * 0.90;
            $m2High = $m2Avg * 1.10;
        }

        $totalLow  = $m2Low  * $m2;
        $totalMid  = $m2Avg  * $m2;
        $totalHigh = $m2High * $m2;

        // Redondear: totales → 50k; mensuales → 500
        $round = ($mode === 'monthly') ? 500 : 50000;
        $totalLow  = (int) round($totalLow  / $round) * $round;
        $totalMid  = (int) round($totalMid  / $round) * $round;
        $totalHigh = (int) round($totalHigh / $round) * $round;

        return [
            'low'        => $totalLow,
            'mid'        => $totalMid,
            'high'       => $totalHigh,
            'per_m2'     => (int) round($m2Avg),
            'confidence' => $confidence,
            'samples'    => $samples,
            'note'       => null,
        ];
    }
}
