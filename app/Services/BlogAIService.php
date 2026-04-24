<?php

namespace App\Services;

use App\Models\Post;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class BlogAIService
{
    // HDV internal URLs for interlinking
    private const INTERNAL_PAGES = [
        'valuacion'   => '/valuacion',
        'propiedades' => '/propiedades',
        'captacion'   => '/vende-tu-propiedad',
        'contacto'    => '/contacto',
        'blog'        => '/blog',
        'renta'       => '/propiedades?tipo=renta',
    ];

    public function __construct(private readonly AIManager $ai) {}

    // ──────────────────────────────────────────────────────────────────────────
    // TOPIC DISCOVERY
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Discover blog topic ideas using Perplexity web search + Claude synthesis.
     * Returns up to 10 ideas sorted by relevance_score desc.
     */
    public function discoverTopics(string $freeText = ''): array
    {
        $contextLine = $freeText ? " Enfócate especialmente en: {$freeText}." : '';

        // Step 1: Perplexity fetches real market data
        $searchQuery = "Tendencias mercado inmobiliario México 2025: precios por zona CDMX Guadalajara Querétaro, tasas hipotecarias Banxico, demanda compradores vs vendedores, noticias recientes sector inmobiliario.{$contextLine}";

        $marketData = '';
        try {
            $marketData = $this->ai->search($searchQuery);
        } catch (\Throwable $e) {
            Log::warning('BlogAIService: Perplexity topic discovery failed', ['error' => $e->getMessage()]);
        }

        $marketBlock = $marketData
            ? "Datos reales del mercado obtenidos hoy:\n{$marketData}"
            : "Usa tu conocimiento general del mercado inmobiliario en México.";

        $freeTextBlock = $freeText ? "\nEl editor quiere ideas relacionadas con: \"{$freeText}\"." : '';

        // Step 2: Claude synthesizes 10 blog topic ideas
        $prompt = <<<PROMPT
Eres un estratega de contenido SEO para Home del Valle, inmobiliaria boutique en México (CDMX, Guadalajara, Querétaro), especializada en propiedades de lujo y de uso mixto.

{$marketBlock}
{$freeTextBlock}

Genera exactamente 8 ideas de artículos de blog orientadas a SEO en Google México. Prioriza:
- Temas con alta intención de búsqueda (compra, venta, valuación, inversión)
- Respuestas a preguntas frecuentes de compradores/vendedores mexicanos
- Temas estacionales o de actualidad basados en los datos del mercado
- Contenido que posicione a HDV como experto de confianza

Devuelve SOLO un array JSON válido con exactamente 8 objetos, sin texto adicional:
[
  {
    "title": "Título SEO del artículo (máx 70 chars, incluye keyword principal)",
    "description": "De qué trataría el artículo, en 2-3 oraciones. Incluye el ángulo SEO.",
    "reasoning": "Por qué este tema es relevante AHORA según datos del mercado (1-2 oraciones)",
    "suggested_keywords": ["keyword principal", "kw secundaria 1", "kw secundaria 2"],
    "relevance_score": 87
  }
]
PROMPT;

        try {
            $raw     = $this->ai->complete($prompt, null, ['temperature' => 0.65]);
            $topics  = $this->parseTopicsArray($raw);
            // Attach the perplexity context so it can be reused when generating the post
            return array_map(fn($t) => array_merge($t, ['_market_data' => $marketData]), $topics);
        } catch (\Throwable $e) {
            Log::warning('BlogAIService: topic synthesis failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BLOG POST GENERATION
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate a full SEO blog post from a topic.
     * Returns structured array ready to be saved to the Post model.
     */
    public function generate(string $title, array $keywords, string $marketData = ''): array
    {
        $system = $this->buildSystemPrompt();
        $prompt = $this->buildGenerationPrompt($title, $keywords, $marketData);

        $raw    = $this->ai->complete($prompt, $system, ['temperature' => 0.70, 'max_tokens' => 8192]);
        $parsed = $this->parseJsonBlock($raw);

        if (empty($parsed['body'])) {
            throw new RuntimeException('La IA no devolvió contenido de blog. Respuesta: ' . substr($raw, 0, 500));
        }

        return $this->normalizeGeneratedPost($parsed);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private: prompts
    // ──────────────────────────────────────────────────────────────────────────

    private function buildSystemPrompt(): string
    {
        return <<<SYSTEM
Eres un experto en SEO y redacción de contenidos para Home del Valle, inmobiliaria boutique en México con presencia en CDMX, Guadalajara y Querétaro.

Tu misión: generar artículos de blog que posicionen en Google México para búsquedas de compradores y vendedores de propiedades de lujo y vivienda en México.

Reglas de escritura:
- Español neutro latinoamericano. Tono profesional, cercano, aspiracional. Sin jerga corporativa.
- Párrafos cortos (máx 4 oraciones). Lenguaje directo. Usa "tú" para dirigirte al lector.
- Estructura clara: introducción con gancho, desarrollo con H2/H3, cierre con CTA.
- Incluye datos concretos (precios, porcentajes, plazos) cuando tengas información del mercado.
- Los CTAs deben ofrecer valor real (valuación gratuita, asesoría sin costo, etc.).
- No repitas el keyword principal más de una vez cada 150 palabras.

Reglas SEO:
- Keyword principal en: primer párrafo, al menos un H2, meta_title, meta_description.
- Meta title: máx 60 caracteres, incluye keyword + Home del Valle o HDV.
- Meta description: máx 155 caracteres, keyword + beneficio concreto + CTA implícito.
- Slug: solo letras minúsculas y guiones, máx 60 caracteres.
- Schema: Article para noticias/guías, HowTo para tutoriales de proceso, FAQPage si hay sección FAQ.
- Interlinking: usa exactamente los URLs internos que se te especifican.
- Cada artículo debe tener mínimo 1200 palabras en el body.

Formato de respuesta: SIEMPRE JSON válido sin markdown ni texto fuera del JSON.
SYSTEM;
    }

    private function buildGenerationPrompt(string $title, array $keywords, string $marketData): string
    {
        $keywordList    = implode(', ', $keywords);
        $focusKeyword   = $keywords[0] ?? $title;
        $marketBlock    = $marketData
            ? "Datos reales del mercado (usa estos en el artículo):\n{$marketData}"
            : '';

        $internalUrls = collect(self::INTERNAL_PAGES)
            ->map(fn($url, $key) => "  - {$key}: {$url}")
            ->implode("\n");

        return <<<PROMPT
Genera un artículo de blog SEO completo para Home del Valle sobre el siguiente tema:

Título propuesto: "{$title}"
Keyword principal: {$focusKeyword}
Keywords secundarias: {$keywordList}
{$marketBlock}

URLs internas disponibles para interlinking:
{$internalUrls}

Genera el siguiente JSON (sin texto fuera del JSON):
{
  "title": "H1 definitivo (máx 70 chars, incluye keyword principal)",
  "meta_title": "Meta title SEO (máx 60 chars, incluye keyword + HDV)",
  "meta_description": "Meta description (máx 155 chars, keyword + beneficio + acción implícita)",
  "slug": "url-slug-del-articulo",
  "focus_keyword": "keyword principal exacta",
  "secondary_keywords": ["kw2", "kw3", "kw4", "kw5"],
  "excerpt": "Resumen del artículo en 2-3 oraciones para cards y RSS",
  "reading_time": 6,
  "seo_score": 84,
  "schema_type": "Article",
  "body": "<p>Cuerpo HTML completo del artículo (mínimo 1200 palabras)...</p>",
  "ctas": [
    {
      "title": "Título del CTA 1",
      "description": "Descripción de valor del CTA 1 (1-2 oraciones)",
      "button_text": "Texto del botón",
      "link": "/valuacion"
    },
    {
      "title": "Título del CTA 2",
      "description": "Descripción de valor del CTA 2",
      "button_text": "Texto del botón",
      "link": "/contacto"
    },
    {
      "title": "Título del CTA 3",
      "description": "Descripción de valor del CTA 3",
      "button_text": "Texto del botón",
      "link": "/propiedades"
    }
  ],
  "internal_links": [
    {
      "anchor": "texto del enlace",
      "url": "/valuacion",
      "context": "frase donde aparece en el body"
    }
  ],
  "image_prompts": {
    "featured": "Prompt DALL-E para imagen principal del artículo. Photorealistic, cinematic, professional real estate photography, Mexico City / Guadalajara luxury property, relevant to article topic. No text, no watermarks. 16:9 landscape.",
    "interior_1": "Prompt DALL-E para imagen de cuerpo 1. Mismo estilo fotográfico, escena relevante al subtema de la sección media del artículo.",
    "interior_2": "Prompt DALL-E para imagen de cuerpo 2. Escena complementaria, diferente ángulo o espacio.",
    "interior_3": "Prompt DALL-E para imagen de cuerpo 3. Escena final, puede ser de lifestyle o exterior."
  }
}

Instrucciones para el body HTML:
- Usa: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a href="...">
- Inserta exactamente {{CTA1}} después del primer H2, {{CTA2}} a mitad del artículo, {{CTA3}} antes del cierre.
- Incluye los internal_links como <a href="url">anchor</a> en el texto natural.
- Cierra con un párrafo de conclusión + llamada a la acción.
- Si el schema_type es FAQPage, incluye una sección <h2>Preguntas frecuentes</h2> con 4-5 pares de pregunta/respuesta.
- Si el schema_type es HowTo, estructura con pasos numerados.
PROMPT;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private: parsing
    // ──────────────────────────────────────────────────────────────────────────

    private function normalizeGeneratedPost(array $data): array
    {
        return [
            'title'               => Str::limit($data['title'] ?? '', 255),
            'meta_title'          => Str::limit($data['meta_title'] ?? '', 60),
            'meta_description'    => Str::limit($data['meta_description'] ?? '', 155),
            'slug'                => Str::slug($data['slug'] ?? $data['title'] ?? ''),
            'focus_keyword'       => $data['focus_keyword'] ?? null,
            'secondary_keywords'  => is_array($data['secondary_keywords'] ?? null) ? $data['secondary_keywords'] : [],
            'excerpt'             => $data['excerpt'] ?? null,
            'reading_time'        => min(60, max(1, (int) ($data['reading_time'] ?? 5))),
            'seo_score'           => min(100, max(0, (int) ($data['seo_score'] ?? 70))),
            'schema_type'         => in_array($data['schema_type'] ?? '', ['Article','HowTo','FAQPage']) ? $data['schema_type'] : 'Article',
            'body'                => $data['body'] ?? '',
            'ctas'                => $this->normalizeCtas($data['ctas'] ?? []),
            'internal_links'      => is_array($data['internal_links'] ?? null) ? $data['internal_links'] : [],
            'image_prompts'       => [
                'featured'   => $data['image_prompts']['featured']   ?? null,
                'interior_1' => $data['image_prompts']['interior_1'] ?? null,
                'interior_2' => $data['image_prompts']['interior_2'] ?? null,
                'interior_3' => $data['image_prompts']['interior_3'] ?? null,
            ],
            'ai_generated'        => true,
        ];
    }

    private function normalizeCtas(array $ctas): array
    {
        return array_values(array_slice(array_map(function ($cta) {
            return [
                'title'       => $cta['title']       ?? '',
                'description' => $cta['description'] ?? '',
                'button_text' => $cta['button_text'] ?? 'Ver más',
                'link'        => $cta['link']        ?? '/contacto',
            ];
        }, $ctas), 0, 3));
    }

    private function parseTopicsArray(string $raw): array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);
        $start = strpos($clean, '[');
        $end   = strrpos($clean, ']');
        if ($start !== false && $end !== false) {
            $clean = substr($clean, $start, $end - $start + 1);
        }
        $decoded = json_decode($clean, true);
        if (!is_array($decoded)) {
            Log::warning('BlogAIService: parseTopicsArray failed', ['raw' => substr($raw, 0, 400)]);
            return [];
        }
        return array_values(array_filter(array_map(function ($item) {
            $title = trim($item['title'] ?? '');
            if (!$title) return null;
            return [
                'title'              => Str::limit($title, 100),
                'description'        => trim($item['description'] ?? ''),
                'reasoning'          => trim($item['reasoning'] ?? ''),
                'suggested_keywords' => is_array($item['suggested_keywords'] ?? null) ? $item['suggested_keywords'] : [],
                'relevance_score'    => min(100, max(0, (int) ($item['relevance_score'] ?? 50))),
            ];
        }, $decoded)));
    }

    private function parseJsonBlock(string $raw): array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);
        $start = strpos($clean, '{');
        $end   = strrpos($clean, '}');
        if ($start !== false && $end !== false) {
            $clean = substr($clean, $start, $end - $start + 1);
        }
        $decoded = json_decode($clean, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('BlogAIService: JSON parse error', ['raw' => substr($raw, 0, 800)]);
            throw new RuntimeException('La IA devolvió JSON inválido: ' . json_last_error_msg());
        }
        return $decoded;
    }
}
