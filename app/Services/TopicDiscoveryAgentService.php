<?php

namespace App\Services;

use App\Models\Post;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class TopicDiscoveryAgentService
{
    public function __construct(private readonly AIManager $ai) {}

    /**
     * Orchestrate topic discovery from multiple sources.
     * Returns up to 10 de-duplicated topics sorted by relevance_score desc.
     *
     * @param  array  $sources  e.g. ['web', 'blog', 'manual']
     * @param  string $freeText Optional seed topic / context provided by the user
     * @return array<array{title:string, description:string, reasoning:string, type:string, keywords:array, relevance_score:int, source:string}>
     */
    public function discover(array $sources, string $freeText = ''): array
    {
        $all = [];

        if (in_array('web', $sources)) {
            $all = array_merge($all, $this->discoverFromWeb($freeText));
        }

        if (in_array('blog', $sources)) {
            $all = array_merge($all, $this->discoverFromBlog($freeText));
        }

        if (in_array('manual', $sources) && $freeText !== '') {
            $all = array_merge($all, $this->discoverFromFreeText($freeText));
        }

        return $this->deduplicateAndRank($all);
    }

    // ── Source providers ─────────────────────────────────────────────────────

    public function discoverFromWeb(string $extraContext = ''): array
    {
        $contextLine = $extraContext ? "\nContexto adicional del usuario: {$extraContext}" : '';

        $prompt = <<<PROMPT
Eres un estratega de contenido para Home del Valle, una inmobiliaria boutique en Ciudad de México (CDMX) especializada en propiedades residenciales de gama media-alta.
Analiza las tendencias actuales del mercado inmobiliario en CDMX y Mexico.{$contextLine}

Genera exactamente 10 ideas de carruseles para Instagram que sean oportunas, relevantes y de alto engagement para compradores, vendedores e inversionistas inmobiliarios.

Devuelve SOLO un array JSON válido con exactamente 10 objetos, sin texto adicional:
[
  {
    "title": "Título del carrusel (máximo 60 caracteres)",
    "description": "Qué cubriría este carrusel en 2-3 oraciones",
    "reasoning": "Por qué este tema es relevante ahora mismo (1-2 oraciones)",
    "type": "educational|commercial|informative|capture|branding",
    "keywords": ["keyword1", "keyword2", "keyword3"],
    "relevance_score": 85
  }
]

Tipos de carrusel:
- educational: Enseña algo sobre el mercado, proceso de compra/venta, financiamiento
- commercial: Muestra o promueve propiedades específicas
- informative: Datos, estadísticas, reportes del mercado
- capture: Orienta a propietarios que quieren vender/rentar con la agencia
- branding: Muestra los valores, diferenciadores y casos de éxito de la agencia
PROMPT;

        try {
            $raw = $this->ai->search($prompt);
            return $this->parseTopicsArray($raw, 'web');
        } catch (\Throwable $e) {
            Log::warning('TopicDiscoveryAgentService web discovery failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function discoverFromBlog(string $extraContext = ''): array
    {
        $posts = Post::published()
            ->latest('published_at')
            ->limit(20)
            ->get(['title', 'excerpt', 'body']);

        if ($posts->isEmpty()) {
            return [];
        }

        $blogSummary = $posts->map(fn ($p) =>
            "- {$p->title}: " . \Str::limit($p->excerpt ?: \Str::limit(strip_tags($p->body ?? ''), 150), 150)
        )->implode("\n");

        $contextLine = $extraContext ? "\nContexto adicional: {$extraContext}" : '';

        $prompt = <<<PROMPT
Eres un estratega de contenido para Home del Valle, una inmobiliaria boutique en CDMX.{$contextLine}

Aquí están los últimos artículos del blog de la agencia:
{$blogSummary}

Analiza este contenido y genera exactamente 10 ideas de carruseles para Instagram que:
1. Amplíen visualmente temas ya populares del blog
2. Cubran ángulos que faltan en el blog pero son relevantes para redes sociales
3. Transformen contenido largo del blog en formato visual de alto impacto para Instagram
4. Añadan valor a la audiencia del blog con formatos más digestibles

Devuelve SOLO un array JSON válido con exactamente 10 objetos, sin texto adicional:
[
  {
    "title": "Título del carrusel (máximo 60 caracteres)",
    "description": "Qué cubriría este carrusel en 2-3 oraciones",
    "reasoning": "Por qué este tema complementa el blog o llena un vacío (1-2 oraciones)",
    "type": "educational|commercial|informative|capture|branding",
    "keywords": ["keyword1", "keyword2", "keyword3"],
    "relevance_score": 75
  }
]
PROMPT;

        try {
            $raw = $this->ai->complete($prompt, null, ['temperature' => 0.7]);
            return $this->parseTopicsArray($raw, 'blog');
        } catch (\Throwable $e) {
            Log::warning('TopicDiscoveryAgentService blog discovery failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function discoverFromFreeText(string $topic): array
    {
        $prompt = <<<PROMPT
Eres un estratega de contenido para Home del Valle, inmobiliaria boutique en CDMX.

El usuario quiere crear carruseles de Instagram sobre el siguiente tema:
"{$topic}"

Genera exactamente 5 variaciones de carruseles sobre este tema o temas directamente relacionados.
Cada variación debe tener un ángulo distinto (educativo, comercial, de captación, etc.).

Devuelve SOLO un array JSON válido con exactamente 5 objetos, sin texto adicional:
[
  {
    "title": "Título del carrusel (máximo 60 caracteres)",
    "description": "Qué cubriría este carrusel en 2-3 oraciones",
    "reasoning": "Por qué este ángulo funciona bien para Instagram (1-2 oraciones)",
    "type": "educational|commercial|informative|capture|branding",
    "keywords": ["keyword1", "keyword2", "keyword3"],
    "relevance_score": 80
  }
]
PROMPT;

        try {
            $raw = $this->ai->complete($prompt, null, ['temperature' => 0.75]);
            return $this->parseTopicsArray($raw, 'manual');
        } catch (\Throwable $e) {
            Log::warning('TopicDiscoveryAgentService free-text discovery failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function deduplicateAndRank(array $topics): array
    {
        $seen  = [];
        $clean = [];

        foreach ($topics as $topic) {
            $key = \Str::slug($topic['title'] ?? '');
            if ($key && !in_array($key, $seen)) {
                $seen[]  = $key;
                $clean[] = $topic;
            }
        }

        // Sort by relevance_score desc
        usort($clean, fn ($a, $b) => ($b['relevance_score'] ?? 0) <=> ($a['relevance_score'] ?? 0));

        return array_slice($clean, 0, 10);
    }

    private function parseTopicsArray(string $raw, string $source): array
    {
        // Strip markdown fences
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);

        // Find first [ ... last ]
        $start = strpos($clean, '[');
        $end   = strrpos($clean, ']');
        if ($start !== false && $end !== false) {
            $clean = substr($clean, $start, $end - $start + 1);
        }

        $decoded = json_decode($clean, true);

        if (!is_array($decoded)) {
            Log::warning('TopicDiscoveryAgentService could not parse AI response', [
                'source' => $source,
                'raw'    => substr($raw, 0, 500),
            ]);
            return [];
        }

        // Normalise + inject source
        $valid_types = ['commercial', 'educational', 'capture', 'informative', 'branding'];

        return array_values(array_filter(array_map(function ($item) use ($source, $valid_types) {
            $title = trim($item['title'] ?? '');
            if (!$title) return null;
            return [
                'title'           => \Str::limit($title, 100),
                'description'     => trim($item['description'] ?? ''),
                'reasoning'       => trim($item['reasoning'] ?? ''),
                'type'            => in_array($item['type'] ?? '', $valid_types) ? $item['type'] : 'educational',
                'keywords'        => is_array($item['keywords'] ?? null) ? $item['keywords'] : [],
                'relevance_score' => min(100, max(0, (int) ($item['relevance_score'] ?? 50))),
                'source'          => $source,
            ];
        }, $decoded)));
    }
}
