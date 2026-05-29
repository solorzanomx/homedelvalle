<?php

namespace App\Services;

use App\Models\MarketColonia;
use App\Models\MarketZoneSnapshot;

class QuickQuoteService
{
    /**
     * Calcula los 4 escenarios de valor con micro-ajustes por características físicas.
     *
     * @param  int    $coloniaId        market_colonia_id
     * @param  string $propertyType     apartment | house | office | land
     * @param  float  $m2Construction   m² de construcción
     * @param  float  $m2Land           m² de terreno (0 = desconocido)
     * @param  string $ageCategory      new | mid | old
     * @param  int    $exactAge         años exactos de construcción (0 = no conocido)
     * @param  int    $bedrooms         número de recámaras (0 = no conocido)
     * @param  int    $bathrooms        número de baños (0 = no conocido)
     * @param  int    $parking          cajones de estacionamiento (-1 = no conocido)
     */
    public function calculate(
        int    $coloniaId,
        string $propertyType,
        float  $m2Construction,
        float  $m2Land      = 0,
        string $ageCategory = 'mid',
        int    $exactAge    = 0,
        int    $bedrooms    = 0,
        int    $bathrooms   = 0,
        int    $parking     = -1,
    ): array {
        $colonia = MarketColonia::find($coloniaId);
        $zone    = $colonia?->zone;

        if (! $zone || $m2Construction < 10) {
            return ['available' => false, 'reason' => 'Datos insuficientes'];
        }

        // ── Obtener snapshots de mercado ──────────────────────────────
        $saleAptSnap    = $this->snap($zone->id, 'sale', 'apartment', $ageCategory);
        $saleHouseSnap  = $this->snap($zone->id, 'sale', 'house', $ageCategory);
        $rentAptSnap    = $this->snap($zone->id, 'rent', 'apartment', $ageCategory);
        $rentOfficeSnap = $this->snap($zone->id, 'rent', 'office', 'mid');

        $saleSnap = match($propertyType) {
            'house'  => $saleHouseSnap ?? $saleAptSnap,
            'office' => $saleAptSnap,
            default  => $saleAptSnap ?? $saleHouseSnap,
        };
        $rentResSnap = $rentAptSnap;

        // ── Calcular micro-ajustes ────────────────────────────────────
        $adjustments  = $this->buildAdjustments($ageCategory, $exactAge, $bedrooms, $bathrooms, $parking);
        $totalFactor  = $this->compoundFactor($adjustments);
        $adjustLabels = array_filter($adjustments, fn($a) => $a['pct'] != 0.0);

        // ── Escenario 1: Venta como vivienda ──────────────────────────
        $saleResidential = null;
        if ($saleSnap && in_array($propertyType, ['apartment', 'house'])) {
            $saleResidential = $this->priceRange(
                (float) $saleSnap->price_m2_low  * $totalFactor,
                (float) $saleSnap->price_m2_avg  * $totalFactor,
                (float) $saleSnap->price_m2_high * $totalFactor,
                $m2Construction,
                $saleSnap->confidence,
                $saleSnap->sample_size,
            );
            $saleResidential['adjusted'] = $totalFactor !== 1.0;
        }

        // ── Escenario 2: Renta habitacional ───────────────────────────
        $rentResidential = null;
        if ($rentResSnap && in_array($propertyType, ['apartment', 'house'])) {
            // Para renta, los ajustes de parking/recámaras tienen menor impacto
            $rentFactor = 1.0 + ($totalFactor - 1.0) * 0.7;
            $rentResidential = $this->priceRange(
                (float) $rentResSnap->price_m2_low  * $rentFactor,
                (float) $rentResSnap->price_m2_avg  * $rentFactor,
                (float) $rentResSnap->price_m2_high * $rentFactor,
                $m2Construction,
                $rentResSnap->confidence,
                $rentResSnap->sample_size,
                mode: 'monthly',
            );
        }

        // ── Escenario 3: Renta comercial ──────────────────────────────
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
        $constructorFactor = match($ageCategory) {
            'new'   => 0.62,
            'mid'   => 0.72,
            'old'   => 0.83,
            default => 0.72,
        };
        // Constructor no ajusta por características (compra el suelo, no la construcción)
        $saleConstructor = null;
        if ($saleSnap) {
            if ($m2Land > 0) {
                $landFactor = 0.60;
                $saleConstructor = $this->priceRange(
                    (float) $saleSnap->price_m2_low  * $m2Construction / max($m2Land, 1) * $landFactor,
                    (float) $saleSnap->price_m2_avg  * $m2Construction / max($m2Land, 1) * $landFactor,
                    (float) $saleSnap->price_m2_high * $m2Construction / max($m2Land, 1) * $landFactor,
                    $m2Land, $saleSnap->confidence, $saleSnap->sample_size,
                );
                $saleConstructor['note'] = "Basado en {$m2Land} m² de terreno";
            } else {
                $totalLow  = (int) round((float) $saleSnap->price_m2_low  * $m2Construction * $constructorFactor / 50000) * 50000;
                $totalMid  = (int) round((float) $saleSnap->price_m2_avg  * $m2Construction * $constructorFactor / 50000) * 50000;
                $totalHigh = (int) round((float) $saleSnap->price_m2_high * $m2Construction * $constructorFactor / 50000) * 50000;
                $saleConstructor = [
                    'low'        => $totalLow,
                    'mid'        => $totalMid,
                    'high'       => $totalHigh,
                    'per_m2'     => (int) round((float) $saleSnap->price_m2_avg * $constructorFactor),
                    'confidence' => $saleSnap->confidence,
                    'samples'    => $saleSnap->sample_size,
                    'note'       => round($constructorFactor * 100) . '% del valor vivienda · Ingresa m² terreno para mayor precisión',
                ];
            }
        }

        return [
            'available'        => true,
            'zone_name'        => $zone->name,
            'colonia_name'     => $colonia->name,
            'period'           => $saleSnap?->period?->translatedFormat('F Y'),
            'property_type'    => $propertyType,
            'm2_construction'  => $m2Construction,
            'm2_land'          => $m2Land,
            'age_category'     => $ageCategory,
            'exact_age'        => $exactAge,
            'adjustments'      => $adjustLabels,
            'total_adjustment' => round(($totalFactor - 1.0) * 100, 1),
            'sale_residential' => $saleResidential,
            'rent_residential' => $rentResidential,
            'rent_commercial'  => $rentCommercial,
            'sale_constructor' => $saleConstructor,
        ];
    }

    // ─── Micro-ajustes ────────────────────────────────────────────────

    /**
     * Construye el array de ajustes con etiquetas para mostrar al usuario.
     */
    private function buildAdjustments(
        string $ageCategory,
        int    $exactAge,
        int    $bedrooms,
        int    $bathrooms,
        int    $parking,
    ): array {
        $adj = [];

        // 1. Ajuste por edad exacta dentro de la categoría
        if ($exactAge > 0) {
            $agePct = $this->ageSubAdjustment($ageCategory, $exactAge);
            if ($agePct !== 0.0) {
                $adj[] = [
                    'key'   => 'age_fine',
                    'label' => "Antigüedad exacta ({$exactAge} años)",
                    'pct'   => $agePct,
                ];
            }
        }

        // 2. Estacionamiento
        if ($parking >= 0) {
            $parkPct = match(true) {
                $parking === 0 => -0.10,
                $parking === 1 =>  0.00,
                $parking === 2 =>  0.06,
                default        =>  0.10,
            };
            if ($parkPct !== 0.0) {
                $adj[] = [
                    'key'   => 'parking',
                    'label' => $parking === 0
                        ? 'Sin estacionamiento'
                        : "{$parking} cajón" . ($parking > 1 ? 'es' : '') . " de estacionamiento",
                    'pct'   => $parkPct,
                ];
            }
        }

        // 3. Recámaras
        if ($bedrooms > 0) {
            $bedPct = match(true) {
                $bedrooms <= 1 => -0.03,
                $bedrooms === 2 => 0.00,
                $bedrooms === 3 => 0.02,
                default         => 0.03,
            };
            if ($bedPct !== 0.0) {
                $adj[] = [
                    'key'   => 'bedrooms',
                    'label' => "{$bedrooms} recámara" . ($bedrooms > 1 ? 's' : ''),
                    'pct'   => $bedPct,
                ];
            }
        }

        // 4. Baños
        if ($bathrooms > 0) {
            $bathPct = match(true) {
                $bathrooms === 1 => -0.04,
                $bathrooms === 2 =>  0.00,
                default          =>  0.03,
            };
            if ($bathPct !== 0.0) {
                $adj[] = [
                    'key'   => 'bathrooms',
                    'label' => "{$bathrooms} baño" . ($bathrooms > 1 ? 's' : ''),
                    'pct'   => $bathPct,
                ];
            }
        }

        return $adj;
    }

    /**
     * Ajuste fino por edad exacta dentro de la categoría.
     * Un depto de 8 años vale más que uno de 18, aunque ambos sean "seminuevo".
     */
    private function ageSubAdjustment(string $category, int $age): float
    {
        return match($category) {
            'new' => match(true) {
                $age <= 1 =>  0.04,
                $age <= 3 =>  0.02,
                default   =>  0.00,
            },
            'mid' => match(true) {
                $age <= 8  =>  0.05,   // 6-8 años: jóvenes del segmento → premium
                $age <= 12 =>  0.02,   // 9-12 años: bueno
                $age <= 16 =>  0.00,   // 13-16 años: promedio del segmento
                $age <= 18 => -0.04,   // 17-18 años: empezando a envejecer
                default    => -0.07,   // 19-20 años: límite del segmento
            },
            'old' => match(true) {
                $age <= 25 =>  0.00,   // 21-25 años: promedio del segmento
                $age <= 30 => -0.06,   // 26-30 años: mucho mantenimiento
                $age <= 40 => -0.12,   // 31-40 años: considerable deterioro
                default    => -0.18,   // 40+ años: casi valor de suelo
            },
            default => 0.0,
        };
    }

    /**
     * Combina factores de ajuste multiplicativamente.
     */
    private function compoundFactor(array $adjustments): float
    {
        $factor = 1.0;
        foreach ($adjustments as $adj) {
            $factor *= (1.0 + $adj['pct']);
        }
        return round($factor, 4);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function snap(int $zoneId, string $op, string $type, string $age): ?MarketZoneSnapshot
    {
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

    private function priceRange(
        float  $m2Low,
        float  $m2Avg,
        float  $m2High,
        float  $m2,
        string $confidence,
        int    $samples,
        string $mode = 'total',
    ): array {
        // Spread mínimo 20%
        if ($m2Avg > 0 && ($m2High - $m2Low) / $m2Avg < 0.20) {
            $m2Low  = $m2Avg * 0.90;
            $m2High = $m2Avg * 1.10;
        }

        $round = ($mode === 'monthly') ? 500 : 50000;
        return [
            'low'        => (int) round($m2Low  * $m2 / $round) * $round,
            'mid'        => (int) round($m2Avg  * $m2 / $round) * $round,
            'high'       => (int) round($m2High * $m2 / $round) * $round,
            'per_m2'     => (int) round($m2Avg),
            'confidence' => $confidence,
            'samples'    => $samples,
            'note'       => null,
            'adjusted'   => false,
        ];
    }
}
