<?php

namespace App\Services;

use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class BlogAIService
{
    // Canon editorial (docs/posicionamiento-marca.md + reglas acumuladas).
    // Si cambia la estrategia de marca, este bloque es el que se actualiza.
    private const BRAND_CONTEXT = 'Home del Valle, inmobiliaria boutique de la alcaldía Benito Juárez, CDMX. NEGOCIO PRINCIPAL: captar predios/casas en exclusiva y conectarlos con su cartera propia de desarrolladoras y constructoras que buscan terreno para construir ("operamos desde la demanda, no desde la oferta"). Negocio de soporte: compra-venta y renta residencial boutique. Colonias foco: Del Valle (Centro, Norte, Sur), Narvarte (Poniente, Oriente), Nápoles, Portales, Xoco; también Acacias, Álamos, Letrán Valle, General Anaya, Noche Buena, Santa Cruz Atoyac, Vertiz Narvarte, Ciudad de los Deportes.';

    /** URLs internas VIVAS para interlinking y CTAs — por funnel. */
    private const INTERNAL_PAGES = [
        'vender a desarrolladora (FUNNEL PRINCIPAL — dueños de casa/predio)' => '/vende-a-desarrolladora',
        'vender propiedad (opinión de valor gratuita)'                       => '/vende-tu-propiedad',
        'observatorio de precios por colonia'                                => '/precios',
        'comprar (búsqueda asistida)'                                        => '/comprar',
        'rentar para vivir'                                                  => '/rentar',
        'rentar mi inmueble (propietarios)'                                  => '/renta-tu-propiedad',
        'propiedades disponibles'                                            => '/propiedades',
        'contacto'                                                           => '/contacto',
    ];

    /**
     * Las categorías SON el ruteo de conversión: el CTA final de cada post se
     * elige por categoría (ctaMap en blog/show). Categorizar mal = mandar al
     * lector al funnel equivocado.
     */
    private const CATEGORIAS = [
        'zonificacion-desarrollo'   => 'predios, uso de suelo H4/H5/H6, vender a desarrolladora, VRC, potencial constructivo (FUNNEL PRINCIPAL)',
        'herencias-y-sucesiones'    => 'herencias, sucesiones, intestados, coherederos, ISR de herencias (mayor fuente de tráfico; el heredero de casa vieja es lead de predio)',
        'colonias-de-benito-juarez' => 'guías de colonia: vivir/invertir en Del Valle, Narvarte, Nápoles, Portales, Xoco…',
        'mercado-inmobiliario-cdmx' => 'precios, tendencias, análisis de mercado BJ/CDMX',
        'vender-tu-propiedad'       => 'proceso de venta tradicional, tips a vendedores',
        'inversion-inmobiliaria'    => 'inversión inmobiliaria, plusvalía, rendimientos',
        'expertos-insights'         => 'temas legales/notariales/proceso, voz de experto',
    ];

    /** Set canónico de tags — NUNCA inventar tags nuevos. */
    private const TAGS_CANONICOS = ['#VenderAConstructora', '#Herencias', '#Precios', '#ParaPropietarios', '#VenderPropiedad', '#Inversión', '#Narvarte', '#Nápoles', '#DelValle'];

    private const REGLAS_EDITORIALES = <<<'CANON'
REGLAS EDITORIALES INQUEBRANTABLES:
- Di siempre "opinión de valor gratuita" — JAMÁS "valuación gratuita" (la valuación formal la hace un valuador certificado externo y tiene costo).
- CERO cifras de precios inventadas: nunca escribas montos de precio por m² ni valores de propiedades. Refiere siempre al "Observatorio de precios" de Home del Valle (/precios) como la fuente viva de cifras actualizadas.
- Tiempo de venta: "45 a 60 días" cuando aplique mencionarlo.
- El contrato se llama "Acuerdo de Representación" y se presenta como garantía de compromiso mutuo — jamás como imposición.
- Tono: técnico pero cercano, boutique. Habla de predios, potencial de desarrollo, due diligence desde el cuidado del patrimonio. Nunca suena a portal masivo de anuncios.
- Jerarquía de marca: ante la duda, el ángulo predio→desarrolladora va primero.
- Español mexicano, "tú", párrafos cortos (máx 4 oraciones).
CANON;

    public function __construct(private readonly AIManager $ai) {}

    // ──────────────────────────────────────────────────────────────────────────
    // TOPIC DISCOVERY
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Descubre temas de blog. $opciones:
     *  - count: número de temas (default 8; una campaña de 30 días pide 30)
     *  - objetivo: brief de la campaña en texto libre
     *  - mezcla: proporciones deseadas (default: la jerarquía de marca)
     *  - lecciones: bitácora editorial (aprendizajes de campañas previas)
     */
    public function discoverTopics(string $freeText = '', array $opciones = []): array
    {
        $count   = min(40, max(3, (int) ($opciones['count'] ?? 8)));
        $mezcla  = $opciones['mezcla'] ?? '~40% zonificacion-desarrollo (predios→desarrolladoras, el negocio principal), ~20% herencias-y-sucesiones, ~25% colonias-de-benito-juarez, ~15% mercado/venta tradicional';
        $contextLine = $freeText ? " Tema prioritario: {$freeText}." : '';

        // Perplexity — búsqueda real del mercado actual
        $marketData = '';
        try {
            $marketData = $this->ai->search("Mercado inmobiliario Benito Juárez CDMX " . now()->year . ": demanda de terrenos y predios para desarrollo vertical, uso de suelo, colonias Del Valle Narvarte Nápoles Portales, herencias y sucesiones de inmuebles CDMX, tendencias compra-venta y renta.{$contextLine}");
        } catch (\Throwable $e) {
            Log::warning('BlogAIService: Perplexity topic discovery failed', ['error' => $e->getMessage()]);
        }

        // Posts existentes: prohibido duplicar temas (canibalización)
        $existentes = \App\Models\Post::orderByDesc('id')->limit(80)->pluck('title')->implode("\n- ");

        $categorias = collect(self::CATEGORIAS)->map(fn($desc, $slug) => "  - {$slug}: {$desc}")->implode("\n");
        $marketBlock = $marketData ? "Datos actuales del mercado (Perplexity):\n{$marketData}" : 'Usa tu conocimiento del mercado de Benito Juárez, CDMX.';
        $objetivoBlock = !empty($opciones['objetivo']) ? "\nOBJETIVO DE LA CAMPAÑA: {$opciones['objetivo']}" : '';
        $leccionesBlock = !empty($opciones['lecciones']) ? "\nLECCIONES EDITORIALES (de campañas previas — respétalas):\n{$opciones['lecciones']}" : '';
        $freeTextBlock = $freeText ? "\nEl editor pide ideas sobre: \"{$freeText}\"." : '';
        $brand = self::BRAND_CONTEXT;
        $reglas = self::REGLAS_EDITORIALES;

        $prompt = <<<PROMPT
Eres el estratega de contenido SEO de {$brand}
{$objetivoBlock}{$leccionesBlock}{$freeTextBlock}

{$marketBlock}

{$reglas}

CATEGORÍAS DISPONIBLES (cada tema DEBE asignarse a una — la categoría decide a qué funnel se manda al lector):
{$categorias}

MEZCLA OBJETIVO de la tanda: {$mezcla}

POSTS QUE YA EXISTEN (prohibido proponer temas duplicados o que compitan por la misma keyword):
- {$existentes}

Genera exactamente {$count} ideas de artículos para SEO local en Google México: búsquedas reales con intención en Benito Juárez, sin canibalizarse entre sí (cada tema con keyword principal DISTINTA).

Devuelve SOLO un array JSON válido con exactamente {$count} objetos:
[
  {
    "title": "Título SEO (máx 70 chars, keyword local)",
    "description": "De qué trata, en 2-3 oraciones",
    "reasoning": "Por qué es relevante AHORA (1-2 oraciones)",
    "suggested_keywords": ["keyword principal", "kw2", "kw3"],
    "categoria": "slug de la categoría asignada",
    "relevance_score": 87
  }
]
PROMPT;

        $raw    = $this->ai->agent('blog.topics', $prompt);
        $topics = $this->parseTopicsArray($raw);

        return array_map(fn($t) => array_merge($t, ['_market_data' => $marketData]), $topics);
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

        $raw    = $this->ai->agent('blog.generation', $prompt, $system);
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
        $brand  = self::BRAND_CONTEXT;
        $reglas = self::REGLAS_EDITORIALES;
        $tags   = implode(', ', self::TAGS_CANONICOS);

        return <<<SYSTEM
Eres el redactor experto en SEO local de {$brand}

Tu misión: artículos que posicionen en Google México búsquedas locales de Benito Juárez, alineados a la jerarquía de marca (predios→desarrolladoras primero) y que conviertan lectores en leads.

{$reglas}

TAGS: elige 2-4 EXCLUSIVAMENTE de este set canónico (prohibido inventar otros): {$tags}

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

        $categorias = collect(self::CATEGORIAS)->map(fn($desc, $slug) => "  - {$slug}: {$desc}")->implode("\n");

        return <<<PROMPT
Genera un artículo de blog SEO completo para Home del Valle sobre:

Título propuesto: "{$title}"
Keyword principal: {$focusKeyword}
Keywords secundarias: {$keywordList}
Zona geográfica: Benito Juárez, CDMX
{$briefSection}
{$marketBlock}

CATEGORÍAS (elige UNA — decide el CTA final que verá el lector, escoge la del funnel correcto):
{$categorias}

URLs internas para interlinking y CTAs (elige las del funnel del artículo; el funnel principal /vende-a-desarrolladora SIEMPRE que el tema toque predios, terrenos, casas viejas, herencias de casas o uso de suelo):
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
  "categoria": "slug de la categoría (de la lista dada)",
  "tags": ["#TagCanonico1", "#TagCanonico2"],
  "body": "<p>HTML completo del artículo (mínimo 1200 palabras)...</p>",
  "ctas": [
    {"title": "...", "description": "...", "button_text": "...", "link": "/vende-a-desarrolladora"},
    {"title": "...", "description": "...", "button_text": "...", "link": "/vende-tu-propiedad"},
    {"title": "...", "description": "...", "button_text": "...", "link": "/precios"}
  ],
  "internal_links": [
    {"anchor": "texto del enlace", "url": "/precios", "context": "frase donde aparece"}
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
            'categoria'          => array_key_exists($data['categoria'] ?? '', self::CATEGORIAS) ? $data['categoria'] : null,
            'tags'               => array_values(array_intersect((array) ($data['tags'] ?? []), self::TAGS_CANONICOS)),
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
        // Quitar prosa antes del array; el cierre se busca desde el final (los
        // objetos traen arrays internos, así que un ']' intermedio no sirve de ancla
        // si la respuesta llegó cortada por max_tokens).
        $start = strpos($clean, '[');
        if ($start !== false) {
            $clean = substr($clean, $start);
        }
        $end     = strrpos($clean, ']');
        $decoded = $end !== false ? json_decode(substr($clean, 0, $end + 1), true) : null;

        // Respuesta truncada: rescatar los objetos que sí llegaron completos.
        if (!is_array($decoded)) {
            $lastComplete = strrpos($clean, '}');
            if ($lastComplete !== false) {
                $decoded = json_decode(substr($clean, 0, $lastComplete + 1) . ']', true);
                if (is_array($decoded)) {
                    Log::warning('BlogAIService: respuesta de temas truncada, rescatados ' . count($decoded) . ' temas completos');
                }
            }
        }

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
                'categoria'          => array_key_exists($item['categoria'] ?? '', self::CATEGORIAS) ? $item['categoria'] : null,
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
