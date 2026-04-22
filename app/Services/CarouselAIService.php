<?php

namespace App\Services;

use App\Models\CarouselPost;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CarouselAIService
{
    // Slide types available per carousel type
    private const SLIDE_TYPES_BY_CAROUSEL = [
        'commercial'  => ['cover', 'benefit', 'benefit', 'social_proof', 'cta'],
        'educational' => ['cover', 'problem', 'explanation', 'key_stat', 'benefit', 'cta'],
        'capture'     => ['cover', 'problem', 'benefit', 'benefit', 'social_proof', 'cta'],
        'informative' => ['cover', 'key_stat', 'explanation', 'explanation', 'cta'],
        'branding'    => ['cover', 'benefit', 'social_proof', 'example', 'cta'],
    ];

    public function __construct(private readonly AIManager $ai) {}

    /**
     * Generate the full carousel structure (slides + captions + hashtags) from a prompt.
     * Returns an array ready to be used by GenerateCarouselFromAIAction.
     */
    public function generate(CarouselPost $carousel, array $context = []): array
    {
        $slideTypes   = self::SLIDE_TYPES_BY_CAROUSEL[$carousel->type] ?? self::SLIDE_TYPES_BY_CAROUSEL['educational'];
        $systemPrompt = $this->buildSystemPrompt();
        $userPrompt   = $this->buildUserPrompt($carousel, $slideTypes, $context);

        $raw = $this->ai->complete($userPrompt, $systemPrompt, ['temperature' => 0.75]);

        $parsed = $this->parseResponse($raw);

        if (empty($parsed['slides'])) {
            throw new RuntimeException('La IA no devolvió slides válidos. Respuesta: ' . substr($raw, 0, 500));
        }

        return $parsed;
    }

    /**
     * Regenerate only the caption (short + long + hashtags) for an existing carousel.
     */
    public function regenerateCaption(CarouselPost $carousel): array
    {
        $system = $this->buildSystemPrompt();
        $prompt = <<<PROMPT
Reescribe el caption de Instagram para este carrusel inmobiliario.

Título: {$carousel->title}
Tipo: {$carousel->type}
CTA actual: {$carousel->cta}

Responde SOLO con JSON válido con esta estructura:
{
  "caption_short": "Caption de máximo 150 caracteres, gancho poderoso",
  "caption_long": "Caption extendido de 3-5 oraciones, storytelling, cierre con CTA",
  "hashtags": ["hashtag1", "hashtag2", "hashtag3"]
}
No incluyas texto fuera del JSON.
PROMPT;

        $raw    = $this->ai->complete($prompt, $system, ['temperature' => 0.8]);
        $parsed = $this->parseJsonBlock($raw);

        return [
            'caption_short' => $parsed['caption_short'] ?? null,
            'caption_long'  => $parsed['caption_long']  ?? null,
            'hashtags'      => $parsed['hashtags']       ?? [],
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildSystemPrompt(): string
    {
        return <<<SYSTEM
Eres un estratega de contenido especializado en marketing inmobiliario para Instagram.
Tu objetivo es crear carruseles de alta conversión para Home del Valle, una inmobiliaria boutique en Ciudad de México.

Principios:
- Usa lenguaje directo, aspiracional y cercano. Nada corporativo.
- Los titulares deben ser cortos, impactantes y con gancho desde la primera palabra.
- Cada slide tiene UN mensaje claro. Sin información repetida entre slides.
- El CTA siempre cierra con urgencia o beneficio concreto.
- Escribe en español neutro latinoamericano (sin "vosotros", sin tuteo formal excesivo).
- Los hashtags deben ser relevantes: mezcla de alta búsqueda y nicho (#bienesraíces, #cdmx, #departamentos, etc.).

Formato de respuesta: SIEMPRE JSON válido, sin markdown, sin texto extra antes o después.
SYSTEM;
    }

    private function buildUserPrompt(CarouselPost $carousel, array $slideTypes, array $context): string
    {
        $typeName = match ($carousel->type) {
            'commercial'  => 'Comercial (venta/renta de propiedad específica)',
            'educational' => 'Educativo (enseña algo al cliente potencial)',
            'capture'     => 'Captación (atraer dueños que quieran vender/rentar)',
            'informative' => 'Informativo (datos del mercado inmobiliario)',
            'branding'    => 'Branding (posicionar Home del Valle como expertos)',
            default       => $carousel->type,
        };

        $slideCount = count($slideTypes);
        $slidesSpec = implode("\n", array_map(
            fn($i, $type) => "  - Slide " . ($i + 1) . ": tipo \"$type\"",
            array_keys($slideTypes),
            $slideTypes
        ));

        $contextBlock = '';
        if (!empty($context['property_data'])) {
            $contextBlock .= "\nDatos de la propiedad:\n" . $context['property_data'];
        }
        if (!empty($context['market_data'])) {
            $contextBlock .= "\nDatos de mercado:\n" . $context['market_data'];
        }
        if (!empty($context['extra'])) {
            $contextBlock .= "\nContexto adicional:\n" . $context['extra'];
        }

        $cta = $carousel->cta ? "CTA deseado: {$carousel->cta}" : 'CTA: invita a agendar visita o escribir por WhatsApp';

        return <<<PROMPT
Crea un carrusel de Instagram para Home del Valle con estos parámetros:

Título/Tema: {$carousel->title}
Tipo de carrusel: {$typeName}
{$cta}
{$contextBlock}

Estructura requerida ({$slideCount} slides):
{$slidesSpec}

Responde SOLO con este JSON (sin texto antes ni después):
{
  "slides": [
    {
      "order": 1,
      "type": "cover",
      "headline": "Titular principal (máx 8 palabras, impacto inmediato)",
      "subheadline": "Subtítulo de apoyo (máx 12 palabras, opcional)",
      "body": "Texto de cuerpo (máx 40 palabras, solo si el tipo lo requiere)",
      "cta_text": "Texto del botón CTA (solo en slide CTA, máx 5 palabras)"
    }
  ],
  "caption_short": "Caption de Instagram máx 150 caracteres con gancho fuerte",
  "caption_long": "Caption extendido, 3-5 oraciones, storytelling + CTA",
  "hashtags": ["bienesraíces", "cdmx", "departamentos", "propiedades", "homedelta"]
}

Reglas estrictas:
- Todos los campos de texto en español.
- body y cta_text son null si no aplican al tipo de slide.
- headline es obligatorio en todos los slides.
- hashtags: array de 8-12 strings sin el símbolo #.
- El JSON debe ser parseable directamente, sin markdown ni comentarios.
PROMPT;
    }

    private function parseResponse(string $raw): array
    {
        $data = $this->parseJsonBlock($raw);

        // Normalize slides
        $slides = [];
        foreach ($data['slides'] ?? [] as $i => $slide) {
            $slides[] = [
                'order'       => (int) ($slide['order'] ?? $i + 1),
                'type'        => $slide['type'] ?? 'explanation',
                'headline'    => $slide['headline'] ?? null,
                'subheadline' => $slide['subheadline'] ?? null,
                'body'        => $slide['body'] ?? null,
                'cta_text'    => $slide['cta_text'] ?? null,
            ];
        }

        return [
            'slides'        => $slides,
            'caption_short' => $data['caption_short'] ?? null,
            'caption_long'  => $data['caption_long']  ?? null,
            'hashtags'      => $data['hashtags']       ?? [],
        ];
    }

    private function parseJsonBlock(string $raw): array
    {
        // Strip possible markdown fences
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);

        // Find first { ... last }
        $start = strpos($clean, '{');
        $end   = strrpos($clean, '}');
        if ($start !== false && $end !== false) {
            $clean = substr($clean, $start, $end - $start + 1);
        }

        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('CarouselAIService: JSON parse failed', ['raw' => substr($raw, 0, 800)]);
            throw new RuntimeException('La IA devolvió JSON inválido: ' . json_last_error_msg());
        }

        return $decoded;
    }

}
