<?php

namespace App\Services;

use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class BlogAIService
{
    // HDV — Benito Juárez, CDMX focus
    private const BRAND_CONTEXT = 'Home del Valle, inmobiliaria boutique especializada en la alcaldía Benito Juárez, CDMX (colonias Del Valle, Narvarte, Portales, Insurgentes, Eje Central, Vertiz Narvarte, Álamos, Letrán Valle, General Anaya, Xoco).';

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

    public function discoverTopics(string $freeText = ''): array
    {
        $contextLine = $freeText ? " Tema prioritario: {$freeText}." : '';

        // Step 1: Perplexity — búsqueda real enfocada en Benito Juárez / CDMX
        $searchQuery = "Mercado inmobiliario Benito Juárez CDMX 2025: precios por metro cuadrado colonias Del Valle Narvarte Portales, demanda departamentos casas, tasas hipotecarias en México, regulaciones CDMX compraventa, tendencias inversión inmobiliaria alcaldía Benito Juárez.{$contextLine}";

        $marketData = '';
        try {
            $marketData = $this->ai->search($searchQuery);
        } catch (\Throwable $e) {
            Log::warning('BlogAIService: Perplexity topic discovery failed', ['error' => $e->getMessage()]);
        }

        $marketBlock   = $marketData
            ? "Datos actuales del mercado en Benito Juárez / CDMX:\n{$marketData}"
            : "Usa tu conocimiento del mercado inmobiliario en Benito Juárez, CDMX.";
        $freeTextBlock = $freeText ? "\nEl editor quiere ideas sobre: \"{$freeText}\"." : '';

        $prompt = <<<PROMPT
Eres un estratega de contenido SEO para {self::BRAND_CONTEXT}

{$marketBlock}
{$freeTextBlock}

Genera exactamente 8 ideas de artículos de blog orientadas a SEO local en Google México. Enfócate en:
- Búsquedas con intención de compra/venta/renta en Benito Juárez y sus colonias
- Preguntas reales de compradores y vendedores en CDMX
- Temas de actualidad según los datos del mercado que acabas de leer
- Contenido que posicione a Home del Valle como la inmobiliaria experta de Benito Juárez

Devuelve SOLO un array JSON válido con exactamente 8 objetos, sin texto adicional:
[
  {
    "title": "Título SEO del artículo (máx 70 chars, keyword local principal)",
    "description": "De qué trataría el artículo en 2-3 oraciones. Ángulo SEO local.",
    "reasoning": "Por qué este tema es relevante AHORA para Benito Juárez (1-2 oraciones)",
    "suggested_keywords": ["keyword local principal", "kw secundaria 1", "kw secundaria 2"],
    "relevance_score": 87
  }
]
PROMPT;

        try {
            $raw    = $this->ai->complete($prompt, null, ['temperature' => 0.65]);
            $topics = $this->parseTopicsArray($raw);
            return array_map(fn($t) => array_merge($t, ['_market_data' => $marketData]), $topics);
        } catch (\Throwable $e) {
            Log::warning('BlogAIService: topic synthesis failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BLOG POST GENERATION
    // ──────────────────────────────────────────────────────────────────────────

    public function generate(string $title, array $keywords, string $marketData = '', array $brief = []): array
    {
        // If no market data, run a focused Perplexity search for this specific topic
        if (!$marketData) {
            $focusKw = $keywords[0] ?? $title;
            try {
                $marketData = $this->ai->search(
                    "{$focusKw} Benito Juárez CDMX 2026: precios, tendencias, regulaciones, datos actuales."
                );
            } catch (\Throwable $e) {
                Log::warning('BlogAIService: pre-generation search failed', ['error' => $e->getMessage()]);
            }
        }

        $system = $this->buildSystemPrompt();
        $prompt = $this->buildGenerationPrompt($title, $keywords, $marketData, $brief);

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
Eres un experto en SEO local y redacción de contenidos para Home del Valle, la inmobiliaria boutique de referencia en la alcaldía Benito Juárez, CDMX.

Colonias principales: Del Valle Norte, Del Valle Centro, Del Valle Sur, Narvarte Poniente, Narvarte Oriente, Portales Norte, Portales Sur, Portales Oriente, Insurgentes Mixcoac, Álamos, Letrán Valle, General Anaya, Xoco, Eje Central, Vertiz Narvarte, Noche Buena.

Tu misión: generar artículos que posicionen en Google México para búsquedas locales de compradores y vendedores de propiedades en Benito Juárez y CDMX.

Reglas de escritura:
- Español mexicano natural. Tono profesional, cercano, confiable. Usa "tú".
- Párrafos cortos (máx 4 oraciones). Datos concretos cuando los tengas.
- Menciona colonias y zonas específicas de Benito Juárez cuando sea relevante.
- CTAs con valor real: valuación gratuita, asesoría sin costo, recorridos sin compromiso.

Reglas SEO:
- Keyword local principal en: primer párrafo, al menos un H2, meta_title, meta_description.
- Meta title: máx 60 chars, keyword + "Benito Juárez" o "CDMX" + Home del Valle.
- Meta description: máx 155 chars, keyword local + beneficio + CTA implícito.
- Slug: minúsculas y guiones, máx 60 chars, incluye la keyword.
- Schema: Article para guías/noticias, HowTo para procesos paso a paso, FAQPage si hay preguntas frecuentes.
- Mínimo 1200 palabras en el body.
- Inserta {{IMG1}}, {{IMG2}}, {{IMG3}} en el body donde deben ir las imágenes de sección.

Formato de respuesta: JSON válido, sin markdown, sin texto fuera del JSON.
SYSTEM;
    }

    private function buildGenerationPrompt(string $title, array $keywords, string $marketData, array $brief = []): string
    {
        $keywordList  = implode(', ', $keywords);
        $focusKeyword = $keywords[0] ?? $title;
        $marketBlock  = $marketData
            ? "Datos actuales del mercado en Benito Juárez / CDMX (úsalos en el artículo):\n{$marketData}"
            : '';

        $internalUrls = collect(self::INTERNAL_PAGES)
            ->map(fn($url, $key) => "  - {$key}: {$url}")
            ->implode("\n");

        $audienceBlock   = !empty($brief['audience'])    ? "Audiencia objetivo: {$brief['audience']}"      : '';
        $keyPointsBlock  = !empty($brief['key_points'])  ? "Puntos clave a cubrir:\n{$brief['key_points']}" : '';
        $toneBlock       = !empty($brief['tone'])        ? "Tono: {$brief['tone']}"                         : '';
        $lengthBlock     = !empty($brief['length'])      ? "Longitud objetivo: {$brief['length']}"          : '';
        $faqBlock        = !empty($brief['include_faq']) ? "Incluir sección FAQ al final con 4 preguntas frecuentes. Usar schema_type = FAQPage." : '';

        $briefSection = implode("\n", array_filter([
            $audienceBlock, $keyPointsBlock, $toneBlock, $lengthBlock, $faqBlock,
        ]));

        return <<<PROMPT
Genera un artículo de blog SEO completo para Home del Valle sobre:

Título propuesto: "{$title}"
Keyword principal: {$focusKeyword}
Keywords secundarias: {$keywordList}
Zona geográfica: Benito Juárez, CDMX
{$briefSection}
{$marketBlock}

URLs internas para interlinking:
{$internalUrls}

Devuelve exactamente este JSON (sin texto fuera del JSON):
{
  "title": "H1 definitivo (máx 70 chars, keyword + zona geográfica cuando aplique)",
  "meta_title": "Meta title (máx 60 chars, keyword + Benito Juárez/CDMX + Home del Valle)",
  "meta_description": "Meta description (máx 155 chars, keyword local + beneficio + CTA)",
  "slug": "slug-seo-con-keyword-local",
  "focus_keyword": "keyword principal exacta",
  "secondary_keywords": ["kw2", "kw3", "kw4", "kw5"],
  "excerpt": "Resumen 2-3 oraciones para cards y RSS",
  "reading_time": 6,
  "seo_score": 84,
  "schema_type": "Article",
  "body": "<p>HTML completo del artículo (mínimo 1200 palabras)...</p>",
  "ctas": [
    {"title": "...", "description": "...", "button_text": "...", "link": "/valuacion"},
    {"title": "...", "description": "...", "button_text": "...", "link": "/contacto"},
    {"title": "...", "description": "...", "button_text": "...", "link": "/propiedades"}
  ],
  "internal_links": [
    {"anchor": "texto del enlace", "url": "/valuacion", "context": "frase donde aparece"}
  ],
  "image_prompts": {
    "featured": "Photorealistic cinematic wide-angle shot, luxury real estate in Benito Juárez CDMX, [describe specific scene relevant to article topic], golden hour lighting, modern architecture, tree-lined street, no people, no text, no watermarks, 16:9 landscape, shot on Sony A7R V, professional real estate photography",
    "interior_1": "Photorealistic interior shot, [scene relevant to first main section], modern luxury apartment in Mexico City, natural light, clean minimalist design, no people, no text, 16:9 landscape",
    "interior_2": "Photorealistic [scene relevant to second main section of article], Benito Juárez CDMX, professional photography, warm natural light, no people, no text, 16:9 landscape",
    "interior_3": "Photorealistic lifestyle real estate photo, [scene relevant to conclusion/CTA section], Mexico City neighborhood, aspirational mood, no people, no text, 16:9 landscape"
  }
}

Instrucciones para el body HTML:
- Tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a href="...">
- Coloca {{CTA1}} después del primer H2
- Coloca {{IMG1}} después del segundo H2 (imagen de sección)
- Coloca {{CTA2}} a mitad del artículo
- Coloca {{IMG2}} después del tercer H2
- Coloca {{IMG3}} antes de la sección de conclusión o FAQ
- Coloca {{CTA3}} al final, antes del párrafo de cierre
- Incluye los internal_links como <a href="url">anchor</a> en el texto
- Menciona colonias de Benito Juárez cuando sea natural hacerlo
- En image_prompts, reemplaza los corchetes [describe...] con descripciones específicas al tema del artículo
PROMPT;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private: normalization
    // ──────────────────────────────────────────────────────────────────────────

    private function normalizeGeneratedPost(array $data): array
    {
        return [
            'title'              => Str::limit($data['title'] ?? '', 255),
            'meta_title'         => Str::limit($data['meta_title'] ?? '', 60),
            'meta_description'   => Str::limit($data['meta_description'] ?? '', 155),
            'slug'               => Str::slug($data['slug'] ?? $data['title'] ?? ''),
            'focus_keyword'      => $data['focus_keyword'] ?? null,
            'secondary_keywords' => is_array($data['secondary_keywords'] ?? null) ? $data['secondary_keywords'] : [],
            'excerpt'            => $data['excerpt'] ?? null,
            'reading_time'       => min(60, max(1, (int) ($data['reading_time'] ?? 5))),
            'seo_score'          => min(100, max(0, (int) ($data['seo_score'] ?? 70))),
            'schema_type'        => in_array($data['schema_type'] ?? '', ['Article','HowTo','FAQPage']) ? $data['schema_type'] : 'Article',
            'body'               => $data['body'] ?? '',
            'ctas'               => $this->normalizeCtas($data['ctas'] ?? []),
            'internal_links'     => is_array($data['internal_links'] ?? null) ? $data['internal_links'] : [],
            'image_prompts'      => [
                'featured'   => $data['image_prompts']['featured']   ?? null,
                'interior_1' => $data['image_prompts']['interior_1'] ?? null,
                'interior_2' => $data['image_prompts']['interior_2'] ?? null,
                'interior_3' => $data['image_prompts']['interior_3'] ?? null,
            ],
            'ai_generated' => true,
        ];
    }

    private function normalizeCtas(array $ctas): array
    {
        return array_values(array_slice(array_map(fn($cta) => [
            'title'       => $cta['title']       ?? '',
            'description' => $cta['description'] ?? '',
            'button_text' => $cta['button_text'] ?? 'Ver más',
            'link'        => $cta['link']        ?? '/contacto',
        ], $ctas), 0, 3));
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
