<?php

namespace App\Services;

use App\Models\MarketColonia;
use App\Models\MarketZoneSnapshot;

/**
 * Valuación de terrenos para constructores.
 * Calcula potencial constructivo (COS/CUS), análisis de viabilidad financiera
 * y valor residual del terreno usando el método de desarrollo.
 */
class ConstructorValuationService
{
    // ─── Costos de referencia CDMX 2025 ──────────────────────────────────────

    /** Costo de construcción base $/m² bruto (departamentos medios BJ) */
    const COSTO_CONSTRUCCION_DEFAULT = 22_000;

    /** % de m² brutos que son vendibles (descuenta circulaciones, lobby, muros) */
    const EFICIENCIA_VENDIBLE_DEFAULT = 0.80;

    /** Costos indirectos como % del costo de construcción (proyecto, permisos, supervisión, ventas) */
    const COSTOS_INDIRECTOS_PCT = 0.20;

    /** Gasto financiero como % del valor del terreno (crédito puente ~3 años) */
    const GASTO_FINANCIERO_PCT = 0.15;

    /** ROI mínimo que exige el constructor sobre el total invertido */
    const ROI_MINIMO_VIABLE = 18.0;

    /** ROI objetivo para calcular el valor residual del terreno */
    const ROI_OBJETIVO_RESIDUAL = 22.0;

    /** m² promedio por departamento para estimar unidades */
    const TAMANO_DEPTO_DEFAULT = 65;

    // ─── Catálogo de zonificaciones (PDDU Benito Juárez / CDMX) ─────────────

    const ZONIFICACIONES = [
        'H3_30'  => ['label' => 'H 3/30 · Habitacional 3 pisos',       'cos' => 0.60, 'cus' => 1.80, 'pisos' => 3,  'lote_min' => 30],
        'H4_30'  => ['label' => 'H 4/30 · Habitacional 4 pisos',       'cos' => 0.60, 'cus' => 2.40, 'pisos' => 4,  'lote_min' => 30],
        'HM4_30' => ['label' => 'HM 4/30 · Hab. Mixto 4 pisos',        'cos' => 0.60, 'cus' => 2.40, 'pisos' => 4,  'lote_min' => 30],
        'HM5_30' => ['label' => 'HM 5/30 · Hab. Mixto 5 pisos',        'cos' => 0.60, 'cus' => 3.00, 'pisos' => 5,  'lote_min' => 30],
        'HM6_30' => ['label' => 'HM 6/30 · Hab. Mixto 6 pisos',        'cos' => 0.60, 'cus' => 3.60, 'pisos' => 6,  'lote_min' => 30],
        'HM8_30' => ['label' => 'HM 8/30 · Hab. Mixto 8 pisos',        'cos' => 0.60, 'cus' => 4.80, 'pisos' => 8,  'lote_min' => 30],
        'HC4_30' => ['label' => 'HC 4/30 · Hab. + Comercio 4 pisos',   'cos' => 0.80, 'cus' => 3.20, 'pisos' => 4,  'lote_min' => 30],
        'CB5_30' => ['label' => 'CB 5/30 · Centro de Barrio 5 pisos',  'cos' => 1.00, 'cus' => 5.00, 'pisos' => 5,  'lote_min' => 30],
        'N10'    => ['label' => 'Norma 10 · Lote pequeño < 200 m²',     'cos' => 0.80, 'cus' => 3.20, 'pisos' => 4,  'lote_min' => 30],
        'custom' => ['label' => '✏ Personalizado (ingresa COS y CUS)',  'cos' => null,  'cus' => null,  'pisos' => null, 'lote_min' => null],
    ];

    // ─── Método principal ─────────────────────────────────────────────────────

    /**
     * @param  float  $m2Terreno          m² del terreno
     * @param  float  $cos                Coeficiente de Ocupación del Suelo (0–1)
     * @param  float  $cus                Coeficiente de Utilización del Suelo
     * @param  int    $pisos              Niveles construibles
     * @param  float  $precioTerreno      Precio total del terreno en MXN
     * @param  float  $costoConstruccion  Costo de construcción por m² bruto
     * @param  float  $eficiencia         Factor vendible (0–1)
     * @param  float  $tamanoDepto        m² promedio por departamento
     * @param  float  $precioVentaM2      Precio de venta de los deptos nuevos $/m² (0 = tomar de mercado)
     * @param  ?int   $coloniaId          ID de MarketColonia para precios de mercado
     */
    public function calculate(
        float  $m2Terreno,
        float  $cos,
        float  $cus,
        int    $pisos,
        float  $precioTerreno,
        float  $costoConstruccion = self::COSTO_CONSTRUCCION_DEFAULT,
        float  $eficiencia        = self::EFICIENCIA_VENDIBLE_DEFAULT,
        float  $tamanoDepto       = self::TAMANO_DEPTO_DEFAULT,
        float  $precioVentaM2     = 0,
        ?int   $coloniaId         = null,
    ): array {
        if ($m2Terreno < 10 || $cos <= 0 || $cus <= 0) {
            return ['available' => false, 'reason' => 'Datos insuficientes'];
        }

        // ── Precio de mercado de departamentos nuevos ─────────────────────────
        $precioVentaM2Fuente = 'manual';
        if ($precioVentaM2 <= 0 && $coloniaId) {
            $precioVentaM2 = $this->getMarketPriceM2($coloniaId, 'new');
            $precioVentaM2Fuente = 'observatorio';
        }
        if ($precioVentaM2 <= 0) {
            return ['available' => false, 'reason' => 'No hay precio de venta. Ingresa el precio $/m² o selecciona una colonia con datos del Observatorio.'];
        }

        // ── Potencial constructivo ────────────────────────────────────────────
        $m2Huella          = round($m2Terreno * $cos, 1);
        $m2BrutosTotales   = round($m2Terreno * $cus, 1);
        $m2Vendibles       = round($m2BrutosTotales * $eficiencia, 1);
        $m2Circulacion     = round($m2BrutosTotales * (1 - $eficiencia), 1);
        $deptoEstimados    = $m2Vendibles > 0 ? max(1, (int) floor($m2Vendibles / $tamanoDepto)) : 0;

        // ── Análisis financiero con el precio pedido por el propietario ───────
        $valorVentaTotal        = $m2Vendibles * $precioVentaM2;
        $costoConstruccionTotal = $m2BrutosTotales * $costoConstruccion;
        $costosIndirectos       = $costoConstruccionTotal * self::COSTOS_INDIRECTOS_PCT;
        $gastoFinanciero        = $precioTerreno * self::GASTO_FINANCIERO_PCT;
        $costoTotal             = $precioTerreno + $costoConstruccionTotal + $costosIndirectos + $gastoFinanciero;
        $utilidadNeta           = $valorVentaTotal - $costoTotal;
        $roiConstructor         = $costoTotal > 0 ? ($utilidadNeta / $costoTotal) * 100 : -999;
        $margenSobreVenta       = $valorVentaTotal > 0 ? ($utilidadNeta / $valorVentaTotal) * 100 : 0;

        // ── Valor residual del terreno (¿cuánto debería costar para ser viable?) ─
        $utilidadObjetivo    = $valorVentaTotal * (self::ROI_OBJETIVO_RESIDUAL / 100);
        $costosSinTerreno    = $costoConstruccionTotal + $costosIndirectos;
        // Valor residual = Venta - Construcción - Indirectos - Utilidad objetivo
        // Con gastoFinanciero = terreno × 15%:
        // residual = (Venta - CostosConst - Indirectos - Utilidad) / 1.15
        $valorResidualTerreno  = ($valorVentaTotal - $costosSinTerreno - $utilidadObjetivo) / (1 + self::GASTO_FINANCIERO_PCT);
        $precioResidualM2      = $m2Terreno > 0 ? $valorResidualTerreno / $m2Terreno : 0;

        // ── Indicadores ───────────────────────────────────────────────────────
        $tierraM2Vendible    = $m2Vendibles > 0 ? $precioTerreno / $m2Vendibles : 0;
        $precioTerrenoM2     = $m2Terreno > 0 ? $precioTerreno / $m2Terreno : 0;
        $ratioTierraVenta    = $valorVentaTotal > 0 ? ($precioTerreno / $valorVentaTotal) * 100 : 0;
        $brechaValor         = $valorResidualTerreno - $precioTerreno;   // positivo = terreno barato, negativo = caro
        $brechaPct           = $precioTerreno > 0 ? ($brechaValor / $precioTerreno) * 100 : 0;

        // ── Semáforo de viabilidad ────────────────────────────────────────────
        $viabilidad = $this->calcViabilidad($roiConstructor, $tierraM2Vendible, $ratioTierraVenta);

        // ── Norma 10 notice ───────────────────────────────────────────────────
        $norma10Aplica = $m2Terreno < 200 && $m2Terreno >= 60;

        return [
            'available'           => true,

            // Potencial constructivo
            'm2_terreno'          => $m2Terreno,
            'm2_huella'           => $m2Huella,
            'm2_brutos'           => $m2BrutosTotales,
            'm2_vendibles'        => $m2Vendibles,
            'm2_circulacion'      => $m2Circulacion,
            'pisos'               => $pisos,
            'deptos_estimados'    => $deptoEstimados,
            'eficiencia'          => $eficiencia,

            // Mercado
            'precio_venta_m2'     => (int) round($precioVentaM2),
            'precio_venta_fuente' => $precioVentaM2Fuente,

            // Financiero con precio del propietario
            'valor_venta_total'      => $this->r($valorVentaTotal),
            'costo_construccion'     => $this->r($costoConstruccionTotal),
            'costos_indirectos'      => $this->r($costosIndirectos),
            'gasto_financiero'       => $this->r($gastoFinanciero),
            'costo_sin_terreno'      => $this->r($costosSinTerreno + $gastoFinanciero),
            'costo_total'            => $this->r($costoTotal),
            'utilidad_neta'          => $this->r($utilidadNeta),
            'roi_constructor'        => round($roiConstructor, 1),
            'margen_sobre_venta'     => round($margenSobreVenta, 1),

            // Valor residual
            'valor_residual_terreno' => $this->r($valorResidualTerreno),
            'precio_residual_m2'     => (int) round($precioResidualM2),
            'brecha_valor'           => $this->r($brechaValor),
            'brecha_pct'             => round($brechaPct, 1),

            // Indicadores
            'tierra_m2_vendible'     => (int) round($tierraM2Vendible),
            'precio_terreno_m2'      => (int) round($precioTerrenoM2),
            'ratio_tierra_venta'     => round($ratioTierraVenta, 1),

            // Viabilidad
            'viabilidad'             => $viabilidad,
            'norma10_aplica'         => $norma10Aplica,
        ];
    }

    // ─── Semáforo de viabilidad ───────────────────────────────────────────────

    private function calcViabilidad(float $roi, float $tierraM2Vendible, float $ratioTierraVenta): string
    {
        $puntos = 0;

        // ROI
        if ($roi >= self::ROI_MINIMO_VIABLE) $puntos += 2;
        elseif ($roi >= 12)                   $puntos += 1;

        // Tierra/m² vendible
        if ($tierraM2Vendible <= 8_000)  $puntos += 2;
        elseif ($tierraM2Vendible <= 12_000) $puntos += 1;

        // Ratio tierra/venta
        if ($ratioTierraVenta <= 20)  $puntos += 1;
        elseif ($ratioTierraVenta <= 30) $puntos += 0;

        return match(true) {
            $puntos >= 4 => 'green',
            $puntos >= 2 => 'yellow',
            default      => 'red',
        };
    }

    // ─── Precio de mercado ────────────────────────────────────────────────────

    public function getMarketPriceM2(int $coloniaId, string $ageCategory = 'new'): float
    {
        try {
            $colonia = MarketColonia::find($coloniaId);
            $zone    = $colonia?->zone;
            if (! $zone) return 0;

            $snap = MarketZoneSnapshot::where('market_zone_id', $zone->id)
                ->where('operation_type', 'sale')
                ->where('property_type', 'apartment')
                ->where('age_category', $ageCategory)
                ->orderByDesc('period')
                ->first()
                ?? MarketZoneSnapshot::where('market_zone_id', $zone->id)
                    ->where('operation_type', 'sale')
                    ->where('property_type', 'apartment')
                    ->orderByDesc('period')
                    ->first();

            return $snap ? (float) $snap->price_m2_avg : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getZonificaciones(): array
    {
        return self::ZONIFICACIONES;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Redondea al múltiplo de 10,000 más cercano */
    private function r(float $v): int
    {
        return (int) round($v / 10_000) * 10_000;
    }
}
