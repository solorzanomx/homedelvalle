<?php

namespace App\Services\Valuation;

use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Models\PropertyValuation;
use App\Models\ValuationAdjustment;

class ValuationEngine
{
    public function __construct(private ValuationNarrativeService $narrative) {}

    /**
     * Ejecuta el cálculo completo sobre un PropertyValuation parcialmente lleno.
     * Persiste el resultado (base_price_m2, adjusted_price_m2, totals, adjustments).
     * Luego genera narrativa profesional vía Claude.
     */
    public function calculate(PropertyValuation $valuation): ValuationResult
    {
        // 1. Resolver precio base desde snapshots de mercado
        $snapshot  = $this->resolveSnapshot($valuation);
        $basePrice = $snapshot ? (float) $snapshot->price_m2_avg : 0.0;

        if ($basePrice === 0.0) {
            return ValuationResult::insufficientData($valuation);
        }

        // 2. Construir y aplicar pipeline de ajustes
        $factors     = $this->buildPipeline($valuation);
        $adjusted    = $basePrice;
        $adjustments = [];
        $order       = 1;

        foreach ($factors as $factor) {
            $before   = $adjusted;
            $adjusted = round($adjusted * (1 + $factor['value']), 2);

            $adjustments[] = [
                'sort_order'       => $order++,
                'factor_key'       => $factor['key'],
                'factor_label'     => $factor['label'],
                'adjustment_type'  => 'percent',
                'adjustment_value' => $factor['value'],
                'price_before'     => $before,
                'price_after'      => $adjusted,
                'explanation'      => $factor['explanation'],
            ];
        }

        // 3. Calcular totales (usando m² de construcción, o total si no hay)
        $m2  = (float) ($valuation->input_m2_const ?? $valuation->input_m2_total);
        $mid = (int) round($adjusted * $m2);
        $low = (int) round($mid * 0.92);
        $hig = (int) round($mid * 1.08);

        $diagnosis     = $this->diagnose($adjusted, $basePrice);
        $suggested     = $this->suggestListPrice($mid, $diagnosis);
        $confidence    = $snapshot ? $snapshot->confidence : 'low';

        // 4. Persistir en la valuación
        $valuation->fill([
            'base_price_m2'       => $basePrice,
            'adjusted_price_m2'   => $adjusted,
            'total_value_low'     => $low,
            'total_value_mid'     => $mid,
            'total_value_high'    => $hig,
            'suggested_list_price'=> $suggested,
            'diagnosis'           => $diagnosis,
            'confidence'          => $confidence,
            'snapshot_id'         => $snapshot?->id,
        ])->save();

        // Reemplazar adjustments existentes
        $valuation->adjustments()->delete();
        foreach ($adjustments as $adj) {
            ValuationAdjustment::create(array_merge(['valuation_id' => $valuation->id], $adj));
        }

        $result = new ValuationResult(
            basePrice:     $basePrice,
            adjustedPrice: $adjusted,
            totalLow:      $low,
            totalMid:      $mid,
            totalHigh:     $hig,
            suggested:     $suggested,
            diagnosis:     $diagnosis,
            confidence:    $confidence,
            adjustments:   $adjustments,
            snapshot:      $snapshot,
        );

        // Generate AI professional narrative (non-blocking — fails silently)
        try {
            $this->narrative->generate($valuation->fresh(['adjustments', 'colonia.zone', 'snapshot']));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ValuationEngine: narrative generation skipped', [
                'valuation_id' => $valuation->id,
                'error'        => $e->getMessage(),
            ]);
        }

        return $result;
    }

    // ── Resolución del precio base ─────────────────────────────────────────

    protected function resolveSnapshot(PropertyValuation $v): ?MarketPriceSnapshot
    {
        if (! $v->input_colonia_id) {
            return null;
        }

        $type        = $v->input_type;
        $ageCategory = $v->age_category; // accessor del model

        // Prioridad 1: colonia exacta + tipo + antigüedad
        $snap = MarketPriceSnapshot::where('market_colonia_id', $v->input_colonia_id)
            ->where('property_type', $type)
            ->where('age_category', $ageCategory)
            ->latest('period')
            ->first();
        if ($snap) return $snap;

        // Prioridad 2: colonia exacta + tipo (cualquier antigüedad)
        $snap = MarketPriceSnapshot::where('market_colonia_id', $v->input_colonia_id)
            ->where('property_type', $type)
            ->latest('period')
            ->first();
        if ($snap) return $snap;

        // Prioridad 3: cualquier colonia de la misma zona + tipo + antigüedad
        $zoneId    = MarketColonia::find($v->input_colonia_id)?->market_zone_id;
        if ($zoneId) {
            $coloniaIds = MarketColonia::where('market_zone_id', $zoneId)->pluck('id');
            $snap = MarketPriceSnapshot::whereIn('market_colonia_id', $coloniaIds)
                ->where('property_type', $type)
                ->where('age_category', $ageCategory)
                ->latest('period')
                ->first();
        }

        return $snap; // null si no hay nada
    }

    // ── Pipeline de factores de ajuste ────────────────────────────────────

    protected function buildPipeline(PropertyValuation $v): array
    {
        return array_filter([
            $this->factorAge($v),
            $this->factorCondition($v),
            $this->factorFloorElevator($v),
            $this->factorUnitPosition($v),
            $this->factorOrientation($v),
            $this->factorSeismic($v),
            $this->factorParking($v),
            $this->factorAmenities($v),
            $this->factorSize($v),
        ], fn($f) => $f !== null);
    }

    protected function factorAge(PropertyValuation $v): array
    {
        $years = $v->input_age_years;

        [$value, $desc] = match(true) {
            $years <= 5  => [0.00,  'Inmueble nuevo (0–5 años). Sin depreciación.'],
            $years <= 10 => [-0.05, "Inmueble de {$years} años. Depreciación leve."],
            $years <= 20 => [-0.12, "Inmueble de {$years} años. Depreciación moderada."],
            $years <= 30 => [-0.22, "Inmueble de {$years} años. Depreciación significativa."],
            $years <= 45 => [-0.32, "Inmueble de {$years} años. Depreciación alta."],
            default      => [-0.40, "Inmueble de {$years} años. Depreciación muy alta."],
        };

        return [
            'key'         => 'age_depreciation',
            'label'       => "Antigüedad ({$years} años)",
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorCondition(PropertyValuation $v): array
    {
        [$value, $label, $desc] = match($v->input_condition) {
            'excellent' => [+0.15, 'Excelente / Remodelado',    'Inmueble en condición premium o remodelado recientemente. Prima de conservación.'],
            'good'      => [0.00,  'Bueno',                     'Conservación buena. Sin ajuste.'],
            'fair'      => [-0.08, 'Regular',                   'Requiere mantenimiento o mejoras menores. Descuento aplicado.'],
            'poor'      => [-0.18, 'Necesita remodelación',     'Requiere inversión significativa. Descuento por condición.'],
            default     => [0.00,  'Sin datos',                  ''],
        };

        return [
            'key'         => 'condition',
            'label'       => "Estado de conservación: {$label}",
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorFloorElevator(PropertyValuation $v): ?array
    {
        $floor   = $v->input_floor;
        $elev    = $v->input_has_elevator;

        if ($floor === null) return null;

        if ($elev) {
            $value = $floor >= 5 ? +0.04 : 0.00;
            $desc  = $floor >= 5
                ? "Piso {$floor} con elevador. Prima por altura y vista."
                : "Piso {$floor} con elevador. Sin ajuste.";
        } else {
            [$value, $desc] = match(true) {
                $floor === 1     => [-0.08, "Planta baja sin elevador. Descuento por accesibilidad y privacidad."],
                $floor <= 4      => [-0.04, "Piso {$floor} sin elevador. Descuento moderado."],
                default          => [-0.12, "Piso {$floor} sin elevador. Descuento significativo por acceso."],
            };
        }

        return [
            'key'         => 'floor_elevator',
            'label'       => "Piso {$floor}" . ($elev ? ' con elevador' : ' sin elevador'),
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorUnitPosition(PropertyValuation $v): ?array
    {
        if ($v->input_type !== 'apartment' || !$v->input_unit_position) return null;

        [$value, $label, $desc] = match($v->input_unit_position) {
            'exterior' => [+0.05, 'Departamento exterior', 'Vista a calle o jardín, ventilación e iluminación natural. Prima de mercado.'],
            'interior' => [-0.05, 'Departamento interior', 'Sin vista directa al exterior. Descuento por menor iluminación y privacidad.'],
            default    => [0.00,  '',                       ''],
        };

        return [
            'key'         => 'unit_position',
            'label'       => $label,
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorOrientation(PropertyValuation $v): ?array
    {
        if ($v->input_type !== 'apartment' || !$v->input_orientation) return null;

        // En CDMX: sur/sureste = más luz natural todo el año → prima
        // Norte = menos luz → descuento. Este/Oeste = neutro.
        [$value, $desc] = match($v->input_orientation) {
            'sur'      => [+0.04, 'Orientación sur: máxima luz natural durante todo el año en CDMX. Prima de mercado.'],
            'sureste'  => [+0.03, 'Orientación sureste: excelente luz matutina y tarde. Prima moderada.'],
            'suroeste' => [+0.02, 'Orientación suroeste: buena iluminación vespertina. Prima leve.'],
            'este'     => [+0.01, 'Orientación este: luz matutina. Ajuste mínimo positivo.'],
            'oeste'    => [0.00,  'Orientación oeste: luz vespertina. Sin ajuste significativo.'],
            'noreste'  => [-0.01, 'Orientación noreste: luz limitada. Ajuste mínimo negativo.'],
            'noroeste' => [-0.02, 'Orientación noroeste: poca luz solar directa. Descuento leve.'],
            'norte'    => [-0.04, 'Orientación norte: mínima luz natural en CDMX. Descuento de mercado.'],
            default    => [0.00,  ''],
        };

        $label = ucfirst($v->input_orientation);

        return [
            'key'         => 'orientation',
            'label'       => "Orientación: {$label}",
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorSeismic(PropertyValuation $v): ?array
    {
        if (!$v->input_seismic_status || $v->input_seismic_status === 'none') return null;

        [$value, $label, $desc] = match($v->input_seismic_status) {
            'damaged_repaired' => [
                -0.08,
                'Edificio con daño sísmico reparado',
                'El edificio sufrió daños en sismo y fue reparado. Existe percepción negativa de mercado (factor psicológico) pese a la reparación. Descuento por menor demanda.',
            ],
            'damaged_reinforced' => [
                -0.04,
                'Edificio con daño sísmico y reforzamiento estructural',
                'El edificio fue dañado en sismo pero sometido a reforzamiento estructural certificado. El reforzamiento da garantía técnica; descuento reducido por percepción de mercado residual.',
            ],
            'unknown' => [
                -0.03,
                'Historial sísmico desconocido',
                'No se cuenta con información verificable sobre daño sísmico. Descuento de precaución por incertidumbre.',
            ],
            default => [0.00, '', ''],
        };

        return [
            'key'         => 'seismic_status',
            'label'       => $label,
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorParking(PropertyValuation $v): array    {
        $parking = $v->input_parking;

        [$value, $desc] = match(true) {
            $parking === 0 => [-0.10, 'Sin cajón de estacionamiento. Alta penalización en BJ.'],
            $parking === 1 => [0.00,  '1 cajón de estacionamiento. Estándar de mercado.'],
            $parking === 2 => [+0.06, '2 cajones de estacionamiento. Prima por acceso adicional.'],
            default        => [+0.10, "{$parking} cajones de estacionamiento. Prima por oferta amplia."],
        };

        return [
            'key'         => 'parking',
            'label'       => $parking === 0 ? 'Sin estacionamiento' : "{$parking} cajón(es) de estacionamiento",
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    protected function factorAmenities(PropertyValuation $v): ?array
    {
        $extras = [];
        if ($v->input_has_rooftop)      $extras[] = 'rooftop privado';
        if ($v->input_has_balcony)      $extras[] = 'balcón';
        if ($v->input_has_service_room) $extras[] = 'cuarto de servicio';
        if ($v->input_has_storage)      $extras[] = 'bodega';

        if (empty($extras)) return null;

        // Cada amenidad suma; se limita al total a +8%
        $raw = 0.0;
        if ($v->input_has_rooftop)      $raw += 0.040;
        if ($v->input_has_balcony)      $raw += 0.025;
        if ($v->input_has_service_room) $raw += 0.030;
        if ($v->input_has_storage)      $raw += 0.020;

        $value = min($raw, 0.08); // cap

        return [
            'key'         => 'amenities',
            'label'       => 'Amenidades: ' . implode(', ', $extras),
            'value'       => round($value, 4),
            'explanation' => 'Prima por características adicionales: ' . implode(', ', $extras) . '.',
        ];
    }

    protected function factorSize(PropertyValuation $v): array
    {
        $m2 = (float) ($v->input_m2_const ?? $v->input_m2_total);

        [$value, $desc] = match(true) {
            $m2 < 50   => [+0.05, "Superficie de {$m2}m². Inmuebles compactos tienen alta demanda en BJ."],
            $m2 <= 90  => [0.00,  "Superficie de {$m2}m². Rango estándar de mercado."],
            $m2 <= 150 => [-0.03, "Superficie de {$m2}m². Menor liquidez por tamaño."],
            default    => [-0.06, "Superficie de {$m2}m². Mercado más reducido para inmuebles grandes."],
        };

        return [
            'key'         => 'size',
            'label'       => "Superficie ({$m2}m²)",
            'value'       => $value,
            'explanation' => $desc,
        ];
    }

    // ── Diagnóstico y precio sugerido ─────────────────────────────────────

    protected function diagnose(float $adjusted, float $base): string
    {
        $ratio = $adjusted / $base;

        return match(true) {
            $ratio > 1.05  => 'above_market',
            $ratio < 0.85  => 'opportunity',
            default        => 'on_market',
        };
    }

    protected function suggestListPrice(int $mid, string $diagnosis): int
    {
        $multiplier = match($diagnosis) {
            'on_market'    => 1.04,
            'above_market' => 1.00,
            'opportunity'  => 1.06,
            default        => 1.04,
        };

        // Redondear al millar más cercano
        return (int) (round($mid * $multiplier / 1000) * 1000);
    }
}
