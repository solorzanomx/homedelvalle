<?php

namespace App\Services\Valuation;

use App\Models\PropertyValuation;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class ValuationNarrativeService
{
    public function __construct(private AIManager $ai) {}

    /**
     * Generate a professional AI narrative for a completed valuation.
     * Saves result to valuation->ai_narrative and updates market_trend.
     */
    public function generate(PropertyValuation $valuation): array
    {
        $prompt = $this->buildPrompt($valuation);

        $system = <<<SYSTEM
Eres un valuador inmobiliario profesional con 15 años de experiencia en el mercado residencial de Benito Juárez, Ciudad de México.
Conoces a fondo las dinámicas de colonias como Narvarte, Del Valle, Portales, Álamos y Roma Sur.
Tu análisis es directo, basado en datos, y útil para propietarios que quieren tomar decisiones informadas de venta.
Evitas generalidades. Siempre refieres cifras concretas del análisis proporcionado.
SYSTEM;

        try {
            $raw = $this->ai->complete($prompt, $system, ['max_tokens' => 900]);
            $narrative = $this->parse($raw);
        } catch (\Throwable $e) {
            Log::warning('ValuationNarrativeService: failed', [
                'valuation_id' => $valuation->id,
                'error'        => $e->getMessage(),
            ]);
            return [];
        }

        if (empty($narrative)) {
            return [];
        }

        // Persist
        $valuation->update([
            'ai_narrative' => $narrative,
            'market_trend' => $narrative['market_trend_label'] ?? null,
        ]);

        return $narrative;
    }

    private function buildPrompt(PropertyValuation $valuation): string
    {
        $valuation->loadMissing(['colonia.zone', 'adjustments']);

        $colonia  = $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? 'colonia no especificada';
        $zone     = $valuation->colonia?->zone?->name ?? 'Benito Juárez';
        $type     = $valuation->type_label;
        $m2       = $valuation->effective_m2;
        $m2Type   = $valuation->input_m2_const ? 'construcción' : 'total';
        $age      = $valuation->input_age_years;
        $ageCat   = match($valuation->age_category) {
            'new'  => 'nuevo, 0–10 años',
            'mid'  => 'seminuevo, 10–30 años',
            'old'  => 'antiguo, más de 30 años',
            default => $valuation->age_category,
        };
        $condition = $valuation->condition_label;
        $floor    = $valuation->input_floor ? "Piso {$valuation->input_floor}" : 'Planta baja o no especificado';
        $elevator = $valuation->input_has_elevator ? 'con elevador' : 'sin elevador';
        $parking  = $valuation->input_parking . ' cajón(es)';

        $amenities = collect([
            $valuation->input_has_rooftop      ? 'rooftop privado' : null,
            $valuation->input_has_balcony       ? 'balcón' : null,
            $valuation->input_has_service_room  ? 'cuarto de servicio' : null,
            $valuation->input_has_storage       ? 'bodega' : null,
        ])->filter()->join(', ') ?: 'ninguna';

        $base      = number_format($valuation->base_price_m2 ?? 0);
        $adjusted  = number_format($valuation->adjusted_price_m2 ?? 0);
        $low       = number_format($valuation->total_value_low ?? 0);
        $mid       = number_format($valuation->total_value_mid ?? 0);
        $high      = number_format($valuation->total_value_high ?? 0);
        $suggested = number_format($valuation->suggested_list_price ?? 0);
        $diagnosis = $valuation->diagnosis_label;
        $confidence = ucfirst($valuation->confidence ?? 'baja');

        $adjustmentsList = $valuation->adjustments->map(function ($a) {
            $sign   = $a->adjustment_value >= 0 ? '+' : '';
            $pct    = number_format($a->adjustment_value * 100, 1);
            $before = '$' . number_format($a->price_before);
            $after  = '$' . number_format($a->price_after);
            return "  - {$a->factor_label}: {$sign}{$pct}% ({$before}/m² → {$after}/m²)";
        })->join("\n");

        // Include snapshot market context if available
        $marketContext = '';
        if ($valuation->snapshot?->notes) {
            $marketContext = "\nCONTEXTO DE MERCADO (fuente: análisis de listings recientes):\n" .
                             substr($valuation->snapshot->notes, 0, 800);
        }

        return <<<PROMPT
INMUEBLE A VALUAR:
- Tipo: {$type}
- Colonia: {$colonia}, Zona: {$zone}, Benito Juárez, CDMX
- Superficie: {$m2} m² ({$m2Type})
- Antigüedad: {$age} años ({$ageCat})
- Estado de conservación: {$condition}
- {$floor}, {$elevator}
- Estacionamiento: {$parking}
- Amenidades: {$amenities}

RESULTADO DEL ANÁLISIS CUANTITATIVO:
- Precio base de mercado: \${$base}/m²
- Precio ajustado tras waterfall: \${$adjusted}/m²
- Rango de valor total: \${$low} (mínimo) — \${$mid} (medio) — \${$high} (máximo)
- Precio sugerido de salida: \${$suggested}
- Diagnóstico: {$diagnosis}
- Confianza del modelo: {$confidence}

AJUSTES APLICADOS (waterfall):
{$adjustmentsList}
{$marketContext}

Genera un análisis narrativo profesional en español para este inmueble específico.
Sé concreto: usa las cifras del análisis, nombra la colonia, menciona características específicas.
NO uses frases genéricas como "el mercado inmobiliario es dinámico". Sé directo y útil.

Responde ÚNICAMENTE con este JSON exacto, sin texto adicional ni markdown:
{
  "market_trend_label": "rising",
  "market_context": "2-3 oraciones sobre el mercado actual en {$colonia}: nivel de demanda, velocidad de absorción, tendencia de precios en los últimos 12 meses, comparativa con colonias vecinas de {$zone}.",
  "property_strengths": "1-2 oraciones sobre los factores específicos de ESTE inmueble que más suman a su valor, citando los ajustes positivos calculados.",
  "property_risks": "1 oración sobre el principal factor que limita el valor o puede frenar la venta.",
  "recommendation": "2-3 oraciones de estrategia comercial concreta: precio de salida de \${$suggested}, estrategia de negociación (margen sugerido), tiempo estimado de colocación en días, perfil del comprador más probable.",
  "key_factors": ["factor clave 1 con cifra", "factor clave 2 con cifra", "factor de riesgo con cifra"]
}
PROMPT;
    }

    private function parse(string $raw): array
    {
        if (preg_match('/```(?:json)?\s*(\{[\s\S]*?\})\s*```/u', $raw, $m)) {
            $decoded = json_decode($m[1], true);
        } elseif (preg_match('/\{[\s\S]*\}/u', $raw, $m)) {
            $decoded = json_decode($m[0], true);
        } else {
            $decoded = null;
        }

        if (!is_array($decoded)) {
            Log::warning('ValuationNarrativeService: invalid JSON', [
                'raw' => substr($raw, 0, 400),
            ]);
            return [];
        }

        // Validate required keys
        $required = ['market_trend_label', 'market_context', 'property_strengths', 'recommendation', 'key_factors'];
        foreach ($required as $key) {
            if (empty($decoded[$key])) {
                return [];
            }
        }

        // Normalize market_trend_label
        if (!in_array($decoded['market_trend_label'], ['rising', 'stable', 'falling'])) {
            $decoded['market_trend_label'] = 'stable';
        }

        return $decoded;
    }
}
