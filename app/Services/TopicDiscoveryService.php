<?php

namespace App\Services;

use App\Models\CarouselPost;
use App\Models\Post;
use App\Models\Property;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

class TopicDiscoveryService
{
    public function __construct(private readonly AIManager $ai) {}

    /**
     * Build enriched context array for a CarouselPost before sending to AI generation.
     * Aggregates: property data, blog data, and optionally Perplexity web search.
     */
    public function buildContext(CarouselPost $carousel, bool $useWebSearch = false): array
    {
        $context = [];

        // Source: property
        if ($carousel->source_type === 'property' && $carousel->source_id) {
            $context['property_data'] = $this->extractPropertyData($carousel->source_id);
        }

        // Source: blog post
        if ($carousel->source_type === 'blog_post' && $carousel->source_id) {
            $context['extra'] = $this->extractBlogData($carousel->source_id);
        }

        // Web search enrichment via Perplexity
        if ($useWebSearch && !empty(config('services.perplexity.api_key'))) {
            try {
                $context['market_data'] = $this->fetchMarketData($carousel->title, $carousel->type);
            } catch (\Throwable $e) {
                Log::warning('TopicDiscovery: web search failed', ['error' => $e->getMessage()]);
                // Non-fatal — continue without market data
            }
        }

        return $context;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function extractPropertyData(int $propertyId): string
    {
        $p = Property::find($propertyId);
        if (!$p) {
            return '';
        }

        $parts = [
            "Título: {$p->title}",
            "Tipo: " . ($p->property_type ?? '—'),
            "Operación: " . ($p->operation_type ?? '—'),
            "Precio: " . ($p->formatted_price ?? $p->price),
            "Colonia: " . ($p->colony ?? '—'),
            "Ciudad: " . ($p->city ?? '—'),
        ];

        if ($p->bedrooms)          $parts[] = "Recámaras: {$p->bedrooms}";
        if ($p->bathrooms)         $parts[] = "Baños: {$p->bathrooms}";
        if ($p->area)              $parts[] = "Superficie: {$p->area} m²";
        if ($p->construction_area) $parts[] = "Construcción: {$p->construction_area} m²";
        if ($p->parking)           $parts[] = "Cajones: {$p->parking}";
        if ($p->maintenance_fee)   $parts[] = "Mantenimiento: \${$p->maintenance_fee}/mes";
        if (!empty($p->amenities)) $parts[] = "Amenidades: " . implode(', ', (array) $p->amenities);

        if ($p->description) {
            $parts[] = "Descripción: " . \Illuminate\Support\Str::limit(strip_tags($p->description), 300);
        }

        return implode("\n", $parts);
    }

    private function extractBlogData(int $postId): string
    {
        $post = Post::find($postId);
        if (!$post) {
            return '';
        }

        return implode("\n", array_filter([
            "Artículo: {$post->title}",
            $post->excerpt ? "Resumen: {$post->excerpt}" : null,
            $post->body    ? "Contenido: " . \Illuminate\Support\Str::limit(strip_tags($post->body), 400) : null,
        ]));
    }

    private function fetchMarketData(string $topic, string $carouselType): string
    {
        $typeHint = match ($carouselType) {
            'informative' => 'estadísticas y tendencias del mercado inmobiliario',
            'educational' => 'consejos y datos educativos sobre bienes raíces',
            'capture'     => 'beneficios de vender o rentar con una inmobiliaria profesional',
            default       => 'datos relevantes del mercado inmobiliario',
        };

        $prompt = "Busca {$typeHint} relacionados con: {$topic}. "
            . "Contexto: Ciudad de México, mercado 2024-2025. "
            . "Proporciona 3-5 datos concretos, cifras o tendencias actuales. "
            . "Responde en español, de forma concisa.";

        return $this->ai->search($prompt);
    }
}
