<?php

namespace App\Services\Marketing;

use App\Models\Captacion;
use App\Models\Operation;
use App\Models\PropertyMarketingStrategy;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class PropertyMarketingStrategyService
{
    public function __construct(private AIManager $ai) {}

    /**
     * Genera (o regenera) la estrategia de promoción de la Operation de
     * venta/renta indicada. Nunca truena si la IA falla — regresa null y
     * deja el registro anterior intacto para que el broker pueda reintentar.
     */
    public function generate(Operation $operation): ?PropertyMarketingStrategy
    {
        $prompt = $this->buildPrompt($operation);

        $system = <<<SYSTEM
Eres un estratega de marketing inmobiliario con 15 años de experiencia posicionando propiedades residenciales en Ciudad de México.
Tu trabajo es definir, para un inmueble específico, quién es el comprador o inquilino más probable y cómo debe promoverse para llegar a esa persona lo más rápido posible.
Eres concreto: nunca usas generalidades como "personas que buscan un buen hogar". Describes perfiles reales con datos demográficos, motivaciones y canales específicos.
SYSTEM;

        try {
            $raw = $this->ai->agent('marketing.strategy', $prompt, $system);
            $parsed = $this->parse($raw);
        } catch (\Throwable $e) {
            Log::warning('PropertyMarketingStrategyService: failed', [
                'operation_id' => $operation->id,
                'error'        => $e->getMessage(),
            ]);
            return null;
        }

        if (empty($parsed)) {
            return null;
        }

        return PropertyMarketingStrategy::updateOrCreate(
            ['operation_id' => $operation->id],
            [
                'target_audience'      => $parsed['target_audience'],
                'positioning_summary'  => $parsed['positioning_summary'],
                'recommended_channels' => $parsed['recommended_channels'],
                'key_selling_points'   => $parsed['key_selling_points'],
                'raw_ai_response'      => $parsed,
                'generated_at'         => now(),
                // Regenerar invalida cualquier aprobación previa — el broker
                // debe revisar el nuevo contenido antes de que vuelva a contar.
                'approved_at'          => null,
                'approved_by'          => null,
            ]
        );
    }

    private function buildPrompt(Operation $operation): string
    {
        $operation->loadMissing('property', 'sourceOperation');
        $property = $operation->property;

        $type       = $property?->property_type ?? 'no especificado';
        $opType     = $operation->type === 'renta' ? 'renta' : 'venta';
        $colony     = $property?->colony ?? 'no especificada';
        $city       = $property?->city ?? 'CDMX';
        $price      = $operation->type === 'renta'
            ? ('$' . number_format((float) ($operation->monthly_rent ?? $property?->price ?? 0)) . '/mes')
            : ('$' . number_format((float) ($property?->price ?? 0)));
        $m2         = $property?->construction_area ?? $property?->area ?? '—';
        $bedrooms   = $property?->bedrooms ?? '—';
        $bathrooms  = $property?->bathrooms ?? '—';
        $parking    = $property?->parking ?? '—';
        $amenities  = collect($property?->amenities ?? [])->filter()->implode(', ') ?: 'ninguna registrada';
        $furnished  = $property?->furnished ? 'sí, amueblado' : 'sin amueblar';
        $description = $property?->description ? substr($property->description, 0, 500) : null;

        // Contexto capturado en la captación original (motivo/urgencia del
        // propietario, plan de marketing conversado en la llamada inicial).
        $captacion = $operation->sourceOperation
            ? Captacion::where('operation_id', $operation->sourceOperation->id)->first()
            : null;
        $motivo    = $captacion?->motivo;
        $urgencia  = $captacion?->urgencia;
        $planNotes = $captacion?->marketing_plan;

        $contextLines = collect([
            $motivo ? "- Motivo del propietario para vender/rentar: {$motivo}" : null,
            $urgencia ? "- Urgencia: {$urgencia}" : null,
            $planNotes ? "- Notas de la llamada inicial sobre el plan de comercialización: {$planNotes}" : null,
        ])->filter()->implode("\n");

        return <<<PROMPT
INMUEBLE A PROMOVER ({$opType}):
- Tipo: {$type}
- Ubicación: {$colony}, {$city}
- Precio: {$price}
- Superficie: {$m2} m²
- Recámaras: {$bedrooms}, Baños: {$bathrooms}, Estacionamiento: {$parking}
- Amenidades: {$amenities}
- Estado: {$furnished}
{$description}

CONTEXTO DEL PROPIETARIO:
{$contextLines}

Define la estrategia de promoción de este inmueble específico.

Responde ÚNICAMENTE con este JSON exacto, sin texto adicional ni markdown:
{
  "target_audience": {
    "perfil": "descripción concreta de 1-2 oraciones de quién es el comprador/inquilino más probable",
    "edad_rango": "ej. 30-45 años",
    "ingresos_estimado": "rango de ingreso mensual o nivel socioeconómico estimado",
    "intereses": ["interés o prioridad 1", "interés o prioridad 2", "interés o prioridad 3"]
  },
  "positioning_summary": "2-3 oraciones de cómo posicionar este inmueble frente a la competencia de la zona, qué mensaje central usar",
  "recommended_channels": ["canal 1 (ej. portal específico)", "canal 2", "canal 3"],
  "key_selling_points": ["punto fuerte 1 concreto de este inmueble", "punto fuerte 2", "punto fuerte 3"]
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
            Log::warning('PropertyMarketingStrategyService: invalid JSON', [
                'raw' => substr($raw, 0, 400),
            ]);
            return [];
        }

        $required = ['target_audience', 'positioning_summary', 'recommended_channels', 'key_selling_points'];
        foreach ($required as $key) {
            if (empty($decoded[$key])) {
                return [];
            }
        }

        return $decoded;
    }
}
