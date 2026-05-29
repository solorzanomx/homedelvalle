<?php

namespace App\Services;

use App\Models\MarketColonia;
use App\Models\MarketZoneSnapshot;

/**
 * Valuación de terrenos para constructores — perspectiva de la constructora compradora.
 *
 * LÓGICA CENTRAL: Método Residual
 * ──────────────────────────────────────────────────────────────────────────────
 * El valor del terreno NO es lo que pide el dueño.
 * El valor del terreno ES lo que le queda al developer después de:
 *
 *   Valor de venta total
 *   − Construcción directa
 *   − Indirectos técnicos (proyecto, supervision)
 *   − Permisos y licencias CDMX
 *   − Comercialización (ventas, marketing)
 *   − Financiamiento de la obra
 *   − Financiamiento del terreno (intereses durante el desarrollo)
 *   − Utilidad mínima exigida por la constructora
 *   ═══════════════════════════════════════════════
 *   = Valor máximo que puede pagar por el terreno
 *
 * MÉTRICA PRINCIPAL: Incidencia del Terreno
 * ──────────────────────────────────────────────────────────────────────────────
 * Incidencia = Precio del Terreno / m² Vendibles
 * Expresada también como % del precio de venta por m²
 *
 * Rangos para CDMX / BJ 2025 (calibrados con datos de mercado):
 *   < 12% del precio de venta  → Excelente — compra directa
 *   12 – 18%                   → Aceptable — viable
 *   18 – 25%                   → Caro — negocia el precio
 *   > 25%                      → Inviable — descarta o asociación
 */
class ConstructorValuationService
{
    // ─── Costos de referencia CDMX 2025 (fuente: CEICO-CMIC) ─────────────────

    /** Costo directo de construcción $/m² bruto — vivienda media/semilujо BJ */
    const COSTO_CONSTRUCCION_DEFAULT = 18_000;

    /** Proyecto ejecutivo + supervisión técnica: % sobre costo directo */
    const INDIRECTOS_TECNICOS_PCT = 0.10;

    /** Permisos, licencias, dictámenes, derechos CDMX: % sobre costo directo
     *  (CDMX puede tardar 6-24 meses y cuesta 3-8% del costo directo) */
    const PERMISOS_CDMX_PCT = 0.05;

    /** Comercialización — ventas, marketing, comisiones: % sobre ventas totales */
    const COMERCIALIZACION_PCT = 0.04;

    /** Intereses del crédito constructor: % sobre costo directo de obra
     *  (tasa comercial ~13-14% × ~1.5 años drawdown promedio) */
    const FINANCIERO_OBRA_PCT = 0.10;

    /** Intereses del crédito puente terreno: % sobre valor del terreno
     *  (tasa ~13% × ~2.5 años plazo promedio de desarrollo) */
    const FINANCIERO_TERRENO_PCT = 0.20;

    /** % de m² brutos que son vendibles (descuenta circulación, lobby, instalaciones) */
    const EFICIENCIA_VENDIBLE_DEFAULT = 0.80;

    /** ROI mínimo que exige una constructora sobre el total invertido */
    const ROI_MINIMO_VIABLE = 18.0;

    /** ROI objetivo para calcular el valor residual (lo que deja un proyecto "bueno") */
    const ROI_OBJETIVO_RESIDUAL = 22.0;

    /** m² promedio por departamento para estimar unidades */
    const TAMANO_DEPTO_DEFAULT = 65;

    // ─── Catálogo de zonificaciones PDDU Benito Juárez / CDMX ────────────────

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
        'custom' => ['label' => '✏ Personalizado',                      'cos' => null,  'cus' => null,  'pisos' => null, 'lote_min' => null],
    ];

    // ─── Cálculo principal ────────────────────────────────────────────────────

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

        // ── 1. Precio de venta de departamentos nuevos ────────────────────────
        $precioFuente = 'manual';
        if ($precioVentaM2 <= 0 && $coloniaId) {
            $precioVentaM2 = $this->getMarketPriceM2($coloniaId, 'new');
            $precioFuente  = 'observatorio';
        }
        if ($precioVentaM2 <= 0) {
            return ['available' => false, 'reason' => 'Ingresa el precio de venta $/m² o selecciona una colonia con datos del Observatorio.'];
        }

        // ── 2. Potencial constructivo ─────────────────────────────────────────
        $m2Huella        = round($m2Terreno * $cos, 1);
        $m2BrutosTotales = round($m2Terreno * $cus, 1);
        $m2Vendibles     = round($m2BrutosTotales * $eficiencia, 1);
        $deptos          = $m2Vendibles > 0 ? max(1, (int) floor($m2Vendibles / $tamanoDepto)) : 0;

        // ── 3. Estructura de costos real (sin terreno) ────────────────────────
        $ventas             = $m2Vendibles * $precioVentaM2;

        $construccionDirect = $m2BrutosTotales * $costoConstruccion;
        $indirectosTecnicos = $construccionDirect * self::INDIRECTOS_TECNICOS_PCT;
        $permisosLicencias  = $construccionDirect * self::PERMISOS_CDMX_PCT;
        $comercializacion   = $ventas * self::COMERCIALIZACION_PCT;
        $financieroObra     = $construccionDirect * self::FINANCIERO_OBRA_PCT;
        // Financiamiento terreno: calculado sobre el precio pedido para el análisis real
        $financieroTerreno  = $precioTerreno * self::FINANCIERO_TERRENO_PCT;

        $costosSinTerreno   = $construccionDirect + $indirectosTecnicos + $permisosLicencias
                            + $comercializacion + $financieroObra;

        // ── 4. Análisis con el precio pedido ──────────────────────────────────
        $costoTotal    = $costosSinTerreno + $financieroTerreno + $precioTerreno;
        $utilidadNeta  = $ventas - $costoTotal;
        $roi           = $costoTotal > 0 ? ($utilidadNeta / $costoTotal) * 100 : -999;
        $margenVentas  = $ventas > 0 ? ($utilidadNeta / $ventas) * 100 : 0;

        // ── 5. Método residual: ¿cuánto DEBERÍA costar el terreno? ────────────
        // Residual = Ventas − Costos sin terreno − Financiamiento terreno estimado − Utilidad objetivo
        // Financiamiento terreno sobre el residual mismo (circular, simplificado):
        // Residual × (1 + financiero_pct) = Ventas − Costos sin terreno − Utilidad objetivo
        $utilidadObjetivo   = $ventas * (self::ROI_OBJETIVO_RESIDUAL / 100);
        // Residual / (1 + FINANCIERO_TERRENO_PCT) para despejar el terreno sin financiamiento
        $valorResidualBruto = $ventas - $costosSinTerreno - $utilidadObjetivo;
        $valorResidual      = $valorResidualBruto / (1 + self::FINANCIERO_TERRENO_PCT);
        $valorResidualM2    = $m2Terreno > 0 ? $valorResidual / $m2Terreno : 0;

        // Precio de oferta sugerido = residual × 0.88 (12% margen de negociación / contingencias)
        $precioOferta   = $valorResidual * 0.88;
        $precioOfertaM2 = $m2Terreno > 0 ? $precioOferta / $m2Terreno : 0;
        $brechaOferta   = $precioTerreno - $precioOferta;   // positivo = terreno caro
        $brechaPct      = $precioOferta > 0 ? ($brechaOferta / $precioOferta) * 100 : 0;

        // ── 6. Incidencia del terreno (LA métrica principal) ──────────────────
        $incidencia    = $m2Vendibles > 0 ? $precioTerreno / $m2Vendibles : 0;
        $incidenciaPct = $precioVentaM2 > 0 ? ($incidencia / $precioVentaM2) * 100 : 0;

        // ── 7. Indicadores adicionales ────────────────────────────────────────
        $precioTerrenoM2  = $m2Terreno > 0 ? $precioTerreno / $m2Terreno : 0;
        $ratioTierraVenta = $ventas > 0 ? ($precioTerreno / $ventas) * 100 : 0;

        // ── 8. Veredicto ──────────────────────────────────────────────────────
        $verdict = $this->calcVerdict($incidenciaPct, $roi);

        // ── 9. Esquema de asociación (alternativa si terreno es caro) ─────────
        // El dueño no vende — aporta el terreno al fideicomiso
        // El developer construye y se reparten utilidades según aportación de capital
        $asociacion = null;
        if ($verdict === 'negocia' || $verdict === 'descarta') {
            $valorTerreno       = max($valorResidual, $precioTerreno);
            $capitalDeveloper   = $costosSinTerreno;
            $totalCapital       = $valorTerreno + $capitalDeveloper;
            $splitDono          = $totalCapital > 0 ? round($valorTerreno / $totalCapital * 100) : 35;
            $splitDeveloper     = 100 - $splitDono;
            $utilidadTotal      = $ventas - $costosSinTerreno - ($ventas * 0.05); // 5% ISR/gastos
            $parteDonoAsociacion= $utilidadTotal * ($splitDono / 100);
            $asociacion = [
                'split_dono'       => $splitDono,
                'split_developer'  => $splitDeveloper,
                'parte_dono'       => $this->r($parteDonoAsociacion),
                'equivalente_m2'   => $m2Terreno > 0 ? (int) round($parteDonoAsociacion / $m2Terreno) : 0,
                'utilidad_total'   => $this->r($utilidadTotal),
            ];
        }

        return [
            'available'           => true,

            // Potencial constructivo
            'm2_terreno'          => $m2Terreno,
            'm2_huella'           => $m2Huella,
            'm2_brutos'           => $m2BrutosTotales,
            'm2_vendibles'        => $m2Vendibles,
            'pisos'               => $pisos,
            'deptos_estimados'    => $deptos,
            'eficiencia'          => $eficiencia,

            // Precio de venta
            'precio_venta_m2'     => (int) round($precioVentaM2),
            'precio_venta_fuente' => $precioFuente,

            // Waterfall financiero (con precio pedido)
            'ventas'              => $this->r($ventas),
            'construccion_direct' => $this->r($construccionDirect),
            'indirectos_tecnicos' => $this->r($indirectosTecnicos),
            'permisos_licencias'  => $this->r($permisosLicencias),
            'comercializacion'    => $this->r($comercializacion),
            'financiero_obra'     => $this->r($financieroObra),
            'financiero_terreno'  => $this->r($financieroTerreno),
            'costos_sin_terreno'  => $this->r($costosSinTerreno),
            'costo_total'         => $this->r($costoTotal),
            'utilidad_neta'       => $this->r($utilidadNeta),
            'roi'                 => round($roi, 1),
            'margen_ventas'       => round($margenVentas, 1),

            // Método residual
            'valor_residual'      => $this->r($valorResidual),
            'valor_residual_m2'   => (int) round($valorResidualM2),
            'precio_oferta'       => $this->r($precioOferta),
            'precio_oferta_m2'    => (int) round($precioOfertaM2),
            'brecha_oferta'       => $this->r($brechaOferta),
            'brecha_pct'          => round($brechaPct, 1),

            // ★ Incidencia del terreno (LA métrica principal)
            'incidencia_m2'       => (int) round($incidencia),
            'incidencia_pct'      => round($incidenciaPct, 1),

            // Indicadores secundarios
            'precio_terreno_m2'   => (int) round($precioTerrenoM2),
            'ratio_tierra_venta'  => round($ratioTierraVenta, 1),

            // Veredicto y alternativa
            'verdict'             => $verdict,
            'norma10_aplica'      => ($m2Terreno >= 60 && $m2Terreno < 200),
            'asociacion'          => $asociacion,
        ];
    }

    // ─── Veredicto (basado en incidencia % y ROI) ─────────────────────────────

    /**
     * Veredicto calibrado para CDMX / BJ 2025.
     *
     * El ROI es el indicador primario — es lo que decide la constructora.
     * La incidencia explica por qué el precio es caro o barato, pero no
     * bloquea el negocio si el retorno es suficientemente atractivo.
     *
     * Umbrales CDMX 2025 (tasas 13-14%, riesgo CDMX, plazo 2.5 años):
     *   ROI ≥ 22%  → COMPRA DIRECTA  (excelente retorno, sin negociación)
     *   ROI 17-22% → VIABLE           (buen negocio, procede)
     *   ROI  8-17% → NEGOCIA          (marginal, bajar precio para que funcione)
     *   ROI  < 8%  → DESCARTA         (inviable, considerar asociación)
     */
    private function calcVerdict(float $incidenciaPct, float $roi): string
    {
        if ($roi >= 22) return 'compra_directa';
        if ($roi >= 17) return 'viable';
        if ($roi >= 8)  return 'negocia';
        return 'descarta';
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

    public function getZonificaciones(): array { return self::ZONIFICACIONES; }

    // ─── Parser de clave SEDUVI ───────────────────────────────────────────────

    /**
     * Parsea una clave de zonificación PDDU / SEDUVI CDMX.
     *
     * Formato con variante de zona:  [USO][PISOS] / [ZONA] / [AREA_LIBRE_%]
     *   H4/Z/20 → 20% libre → COS = 0.80 → CUS = 3.20
     *
     * Formato sin variante:  [USO][PISOS] / [LOTE_MIN_m²]
     *   HM 6/30 → lote mín 30m² → COS = 0.60 → CUS = 3.60
     *
     * COS por uso: H/HM/HA=0.60 · HC=0.80 · CB/CE/CS=1.00
     */
    public function parseSedeviCode(string $rawCode): ?array
    {
        $code = strtoupper(trim(preg_replace('/\s+/', ' ', $rawCode)));
        if ($code === '') return null;

        if (! preg_match('/^([A-Z]+)[\s\-]?(\d+)(.*)/i', $code, $m)) return null;

        $uso   = $m[1];
        $pisos = (int) $m[2];
        $rest  = $m[3];

        if ($pisos < 1 || $pisos > 40) return null;

        $areaLibrePct = null;
        $loteMin      = null;
        $zonaVar      = null;

        if (preg_match('/[\/\-\s]([A-Z]+)[\/\-\s]?(\d+)\s*$/', $rest, $zv)) {
            $zonaVar      = $zv[1];
            $areaLibrePct = (int) $zv[2];
        } elseif (preg_match('/[\/\-\s](\d+)\s*$/', $rest, $lm)) {
            $loteMin = (int) $lm[1];
        }

        $cos = ($areaLibrePct !== null && $areaLibrePct > 0 && $areaLibrePct <= 80)
            ? round(1.0 - ($areaLibrePct / 100), 2)
            : match(true) {
                $uso === 'HC'                                    => 0.80,
                in_array($uso, ['CB','CE','CS','CI','CN'])       => 1.00,
                default                                          => 0.60,
            };

        return [
            'uso'        => $uso,
            'pisos'      => $pisos,
            'cos'        => $cos,
            'cus'        => round($cos * $pisos, 2),
            'lote_min'   => $loteMin,
            'area_libre' => $areaLibrePct,
            'zona_var'   => $zonaVar,
        ];
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function r(float $v): int
    {
        return (int) round($v / 10_000) * 10_000;
    }
}
