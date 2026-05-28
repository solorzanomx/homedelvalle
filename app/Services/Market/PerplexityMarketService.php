<?php

namespace App\Services\Market;

use App\Models\MarketColonia;
use App\Models\MarketPromptTemplate;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class PerplexityMarketService
{
    public function __construct(private AIManager $ai) {}

    /**
     * Full two-step pipeline: Perplexity fetches real listings → Claude analyzes.
     *
     * Returns array keyed by age_category (new|mid|old):
     *   ['low' => int, 'avg' => int, 'high' => int]
     * Plus metadata keys: '_reasoning', '_confidence', '_listings_analyzed', '_outliers_excluded', '_raw_listings'
     */
    public function fetchPrices(MarketColonia $colonia, string $propertyType): array
    {
        // Step 1 — Perplexity: get raw real listings
        $rawListings = $this->fetchListings($colonia, $propertyType);

        if (empty($rawListings) || $this->isErrorResponse($rawListings)) {
            Log::error('PerplexityMarketService: no listings found', [
                'colonia'       => $colonia->name,
                'property_type' => $propertyType,
                'raw'           => substr($rawListings, 0, 100),
            ]);
            return [];
        }

        // Step 2 — Claude: filter outliers, compute statistics, generate reasoning
        return $this->analyzePrices($rawListings, $colonia, $propertyType);
    }

    // ── Step 1: Perplexity — fetch real listings ──────────────────────────

    private function fetchListings(MarketColonia $colonia, string $propertyType): string
    {
        $typeLabel = match ($propertyType) {
            'apartment' => 'departamentos',
            'house'     => 'casas',
            default     => $propertyType,
        };

        $fields = "- \"precio\": precio de lista en pesos MXN (número entero)\n"
                . "- \"m2\": metros cuadrados de construcción (NO de terreno)\n"
                . "- \"antiguedad\": años desde su construcción o null\n"
                . "- \"recamaras\": número de recámaras\n"
                . "- \"piso\": número de piso o null";

        $template = MarketPromptTemplate::getPrompt('sale.search');
        $prompt   = str_replace(
            ['{colonia}', '{type_label}', '{fields}'],
            [$colonia->name, $typeLabel, $fields],
            $template
        );

        try {
            $raw = $this->ai->agent('market.fetch', $prompt);
            Log::info('PerplexityMarketService: raw listings', [
                'colonia'       => $colonia->name,
                'property_type' => $propertyType,
                'response'      => substr($raw, 0, 1000),
            ]);
            return $raw;
        } catch (\Throwable $e) {
            Log::error('PerplexityMarketService: Perplexity API error', [
                'colonia' => $colonia->name,
                'error'   => $e->getMessage(),
            ]);
            return '';
        }
    }

    // ── Step 2: Claude — filter, analyze, compute statistics ─────────────

    private function analyzePrices(string $rawListings, MarketColonia $colonia, string $propertyType): array
    {
        $typeLabel = match ($propertyType) {
            'apartment' => 'departamentos',
            'house'     => 'casas',
            default     => $propertyType,
        };

        $system = 'Eres un analista de mercado inmobiliario con experiencia en estadística de bienes raíces en México. '
                . 'Tu tarea es procesar listings crudos y producir estadísticas de precio por m² confiables. '
                . 'Debes ser escéptico: detectar outliers, datos incompletos o inconsistentes.';

        $template = MarketPromptTemplate::getPrompt('sale.analysis');
        $prompt   = str_replace(
            ['{colonia}', '{type_label}', '{listings}'],
            [$colonia->name, $typeLabel, $rawListings],
            $template
        );

        try {
            $raw = $this->ai->agent('market.analysis', $prompt, $system);
        } catch (\Throwable $e) {
            Log::error('PerplexityMarketService: Claude analysis error', [
                'colonia' => $colonia->name,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }

        return $this->parseAnalysis($raw, $rawListings, $colonia->name, $propertyType);
    }

    // ── Renta: pipeline completo ─────────────────────────────────────────

    /**
     * Pipeline Perplexity→Claude para precios de RENTA.
     * Devuelve precio_mensual_m2 por age_category (new/mid/old).
     * Para commercial (office): los tres buckets representan calidad/ubicación.
     */
    public function fetchRentalPrices(MarketColonia $colonia, string $propertyType): array
    {
        $rawListings = $this->fetchRentalListings($colonia, $propertyType);

        // Detectar respuesta vacía O respuesta de error explícita de Perplexity
        if (empty($rawListings) || $this->isErrorResponse($rawListings)) {
            Log::error('PerplexityMarketService[rent]: no listings found', [
                'colonia'       => $colonia->name,
                'property_type' => $propertyType,
                'raw'           => substr($rawListings, 0, 100),
            ]);
            return [];
        }

        return $this->analyzeRentalPrices($rawListings, $colonia, $propertyType);
    }

    private function fetchRentalListings(MarketColonia $colonia, string $propertyType): string
    {
        $isCommercial = in_array($propertyType, ['office', 'commercial']);

        if ($isCommercial) {
            $typeLabel = 'locales comerciales, oficinas y bodegas';
            $fields    = "- \"precio_renta\": renta mensual en MXN\n"
                       . "- \"m2\": metros cuadrados\n"
                       . "- \"tipo\": local / oficina / bodega\n"
                       . "- \"piso\": número de piso o null";
        } else {
            $typeLabel = match($propertyType) {
                'house' => 'casas',
                default => 'departamentos',
            };
            $fields = "- \"precio_renta\": renta mensual en MXN\n"
                    . "- \"m2\": metros cuadrados de construcción\n"
                    . "- \"antiguedad\": años desde construcción o null\n"
                    . "- \"recamaras\": número de recámaras";
        }

        $template = MarketPromptTemplate::getPrompt('rent.search');
        $prompt   = str_replace(
            ['{colonia}', '{type_label}', '{fields}'],
            [$colonia->name, $typeLabel, $fields],
            $template
        );

        try {
            $raw = $this->ai->agent('market.fetch', $prompt);
            Log::info('PerplexityMarketService[rent]: raw listings', [
                'colonia'       => $colonia->name,
                'property_type' => $propertyType,
                'chars'         => strlen($raw),
            ]);
            return $raw;
        } catch (\Throwable $e) {
            Log::error('PerplexityMarketService[rent]: Perplexity error', [
                'colonia' => $colonia->name,
                'error'   => $e->getMessage(),
            ]);
            return '';
        }
    }

    private function analyzeRentalPrices(string $rawListings, MarketColonia $colonia, string $propertyType): array
    {
        $isCommercial = in_array($propertyType, ['office', 'commercial']);

        if ($isCommercial) {
            $typeLabel      = 'locales comerciales / oficinas / bodegas';
            $priceField     = 'precio_renta (renta mensual MXN)';
            $rangeMin       = 3000;
            $rangeMax       = 500000;
            $priceM2Min     = 50;    // $50/m²/mes mínimo razonable BJ
            $priceM2Max     = 800;   // $800/m²/mes máximo razonable BJ
            $hierarchyNote  = 'Para comercial, la jerarquía es: planta baja / ubicación prime > piso superior / secundario.';
            $ageNote        = 'Para inmuebles comerciales, usa el campo "tipo" en lugar de antigüedad para diferenciar categorías: local PB = "new", oficina = "mid", bodega = "old".';
        } else {
            $typeLabel      = match($propertyType) { 'house' => 'casas', default => 'departamentos' };
            $priceField     = 'precio_renta (renta mensual MXN)';
            $rangeMin       = 3000;
            $rangeMax       = 120000;
            $priceM2Min     = 80;    // $80/m²/mes mínimo razonable BJ
            $priceM2Max     = 500;   // $500/m²/mes máximo razonable BJ
            $hierarchyNote  = 'En Benito Juárez, inmuebles nuevos rentan más caro por m² que los viejos.';
            $ageNote        = '"new": 0–10 años, "mid": 11–30 años, "old": >30 años. Sin antigüedad → asignar a "mid".';
        }

        $system = 'Eres un analista de mercado inmobiliario especializado en rentas en Ciudad de México. '
                . 'Tu tarea es procesar listings de renta y calcular precio de renta mensual por m² ($/m²/mes).';

        $template = MarketPromptTemplate::getPrompt('rent.analysis');
        $prompt   = str_replace(
            ['{colonia}', '{type_label}', '{listings}', '{range_min}', '{range_max}', '{price_m2_min}', '{price_m2_max}', '{age_note}', '{hierarchy_note}'],
            [$colonia->name, $typeLabel, $rawListings, $rangeMin, $rangeMax, $priceM2Min, $priceM2Max, $ageNote, $hierarchyNote],
            $template
        );

        try {
            $raw = $this->ai->agent('market.analysis', $prompt, $system);
        } catch (\Throwable $e) {
            Log::error('PerplexityMarketService[rent]: Claude error', [
                'colonia' => $colonia->name,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }

        return $this->parseAnalysis($raw, $rawListings, $colonia->name, $propertyType . '_rent');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Detecta si la respuesta de Perplexity es un mensaje de error
     * en lugar de un array de listings reales.
     */
    private function isErrorResponse(string $raw): bool
    {
        $trimmed = trim($raw);
        // Respuesta de error explícita: {"error": "..."}
        if (str_starts_with($trimmed, '{"error"')) {
            return true;
        }
        // Array vacío
        if ($trimmed === '[]') {
            return true;
        }
        return false;
    }

    // ── Parse Claude response ─────────────────────────────────────────────

    private function parseAnalysis(string $raw, string $rawListings, string $coloniaName, string $propertyType): array
    {
        // Handle markdown code fences (```json ... ```) or bare JSON
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/u', $raw, $m)) {
            $decoded = json_decode($m[1], true);
        } elseif (preg_match('/\{[\s\S]*\}/u', $raw, $m)) {
            $decoded = json_decode($m[0], true);
        } else {
            $decoded = null;
        }

        if (!is_array($decoded)) {
            Log::warning('PerplexityMarketService: invalid Claude JSON', [
                'colonia' => $coloniaName,
                'raw'     => substr($raw, 0, 600),
            ]);
            return [];
        }

        if (isset($decoded['error'])) {
            Log::info('PerplexityMarketService: insufficient data', [
                'colonia' => $coloniaName,
                'reason'  => $decoded['reason'] ?? '—',
            ]);
            return [];
        }

        $prices = $decoded['prices'] ?? [];
        $result = [];

        foreach (['new', 'mid', 'old'] as $cat) {
            if (!isset($prices[$cat]['low'], $prices[$cat]['avg'], $prices[$cat]['high'])) {
                continue;
            }
            $result[$cat] = [
                'low'  => (int) $prices[$cat]['low'],
                'avg'  => (int) $prices[$cat]['avg'],
                'high' => (int) $prices[$cat]['high'],
            ];
        }

        // Sanity check: enforce new >= mid >= old hierarchy.
        // If violated, drop the offending category rather than show wrong data.
        if (isset($result['new'], $result['mid']) && $result['new']['avg'] < $result['mid']['avg']) {
            Log::warning('PerplexityMarketService: new < mid, dropping new category', [
                'colonia' => $coloniaName,
                'new_avg' => $result['new']['avg'],
                'mid_avg' => $result['mid']['avg'],
            ]);
            unset($result['new']);
        }
        if (isset($result['old'], $result['mid']) && $result['old']['avg'] > $result['mid']['avg']) {
            Log::warning('PerplexityMarketService: old > mid, dropping old category', [
                'colonia' => $coloniaName,
                'old_avg' => $result['old']['avg'],
                'mid_avg' => $result['mid']['avg'],
            ]);
            unset($result['old']);
        }

        // Attach metadata for the job to persist
        $result['_meta'] = [
            'confidence'         => $decoded['confidence']      ?? 'low',
            'listings_analyzed'  => $decoded['listings_analyzed'] ?? 0,
            'outliers_excluded'  => $decoded['outliers_excluded']  ?? 0,
            'reasoning'          => $decoded['reasoning']        ?? '',
            'market_context'     => $decoded['market_context']   ?? '',
            'raw_listings'       => $rawListings,
        ];

        return $result;
    }
}
