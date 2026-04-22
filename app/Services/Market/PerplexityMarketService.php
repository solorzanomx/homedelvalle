<?php

namespace App\Services\Market;

use App\Models\MarketColonia;
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

        if (empty($rawListings)) {
            Log::warning('PerplexityMarketService: no listings found', [
                'colonia'       => $colonia->name,
                'property_type' => $propertyType,
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

        $prompt = <<<PROMPT
Busca anuncios ACTUALES de {$typeLabel} en venta en la Colonia {$colonia->name}, alcaldía Benito Juárez, Ciudad de México.

Busca en estos portales: Inmuebles24, Lamudi, Vivanuncios, Propiedades.com, MercadoLibre Inmuebles.

Para cada anuncio que encuentres, extrae exactamente estos datos:
- precio: precio de lista en pesos MXN (número entero, sin comas ni símbolos)
- m2: metros cuadrados de construcción (número decimal, NO metros de terreno)
- antiguedad: años aproximados desde su construcción (número entero, o null si no se menciona)
- recamaras: número de recámaras (número entero)
- piso: número de piso (número entero, o null si no aplica/no se menciona)
- fuente: nombre del portal donde encontraste el anuncio

Devuelve entre 8 y 15 anuncios reales que hayas encontrado en tu búsqueda.

Responde ÚNICAMENTE con un JSON array, sin texto adicional, sin markdown:
[
  {"precio": 3800000, "m2": 78, "antiguedad": 12, "recamaras": 2, "piso": 3, "fuente": "Inmuebles24"},
  {"precio": 4200000, "m2": 95, "antiguedad": 25, "recamaras": 3, "piso": 1, "fuente": "Lamudi"}
]

Si no encuentras suficientes anuncios (menos de 3), responde: {"error": "sin_datos"}
PROMPT;

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

        $system = <<<SYSTEM
Eres un analista de mercado inmobiliario con experiencia en estadística de datos de bienes raíces en México.
Tu tarea es procesar listings crudos de portales inmobiliarios y producir estadísticas de precio por m² confiables.
Debes ser escéptico de los datos: detectar outliers, datos incompletos o inconsistentes.
SYSTEM;

        $prompt = <<<PROMPT
Se te proporcionan listings de {$typeLabel} en venta en la Colonia {$colonia->name}, Benito Juárez, CDMX, obtenidos de portales inmobiliarios.

LISTINGS CRUDOS:
{$rawListings}

INSTRUCCIONES DE ANÁLISIS:

1. VALIDACIÓN INICIAL
   - Descarta listings con precio < 500,000 o > 50,000,000 MXN (probables errores de captura)
   - Descarta listings con m2 < 20 o m2 > 1000 (probables errores)
   - Descarta listings sin precio o sin m2

2. CÁLCULO DE PRECIO/M²
   - Para cada listing válido: precio_m2 = precio / m2
   - Para Benito Juárez, el rango razonable es $30,000–$180,000 MXN/m²

3. DETECCIÓN DE OUTLIERS
   - Calcula la mediana y el IQR de todos los precio_m2
   - Excluye listings donde precio_m2 < (Q1 - 1.5×IQR) o > (Q3 + 1.5×IQR)

4. CLASIFICACIÓN POR ANTIGÜEDAD
   - "new": 0–10 años de construcción
   - "mid": 11–30 años
   - "old": más de 30 años
   - Listings sin antigüedad: asígnalos a "mid" por defecto
   - Si hay pocos listings, agrupa "new" con "mid" antes de reportar estadísticas vacías

5. ESTADÍSTICAS POR CATEGORÍA (solo si hay ≥ 2 listings válidos en el grupo)
   - low = percentil 25 del precio_m2
   - avg = mediana del precio_m2
   - high = percentil 75 del precio_m2
   - Redondea todos los valores al entero más cercano

6. CONFIANZA
   - "high": ≥ 5 listings válidos en la categoría
   - "medium": 2–4 listings válidos
   - "low": 1 listing válido (solo reporta como referencia)

Responde ÚNICAMENTE con este JSON exacto, sin texto adicional ni markdown:
{
  "prices": {
    "new": {"low": 75000, "avg": 82000, "high": 90000},
    "mid": {"low": 65000, "avg": 72000, "high": 80000},
    "old": {"low": 52000, "avg": 58000, "high": 65000}
  },
  "confidence": "high",
  "listings_analyzed": 10,
  "outliers_excluded": 2,
  "price_m2_range": {"min": 62000, "max": 89000},
  "reasoning": "10 listings procesados, 2 outliers excluidos. Mediana seminuevo $72,000/m².",
  "market_context": "Mercado activo, buena liquidez en unidades 2-3 recámaras."
}

Omite categorías sin datos suficientes. Si todos los datos son inválidos, responde: {"error": "datos_insuficientes", "reason": "descripción breve"}
PROMPT;

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
