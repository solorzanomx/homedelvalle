<?php

namespace App\Support;

use App\Models\Post;

/**
 * En qué etapa del funnel está el lector de un post (Fase 2 y 3 del prompt de leads del blog:
 * docs/funcionalidades/blog-ga4-tracking.md, docs/funcionalidades/blog-cta-clusters.md).
 *
 * `posts.cluster` (nullable) es la fuente cuando el asesor lo fija a mano; si está vacío se deriva
 * de `category` + un ajuste por slug para herencias — así casi ningún post necesita edición manual,
 * solo los que la categoría no describe bien. Antes de la Fase 3 el heurístico de "es herencias"
 * vivía duplicado a mano en `blog/_cta-predio.blade.php`; ahora es la única fuente.
 */
class BlogCluster
{
    public const HERENCIAS = 'herencias';
    public const TERRENO_DESARROLLADORA = 'terreno_desarrolladora';
    public const PRECIOS_INVERSION = 'precios_inversion';
    public const GUIAS_COLONIA = 'guias_colonia';
    public const PROCESO_VENTA = 'proceso_venta';

    public const LABELS = [
        self::HERENCIAS              => 'Herencias y sucesiones',
        self::TERRENO_DESARROLLADORA => 'Predio → desarrolladora',
        self::PRECIOS_INVERSION      => 'Precios e inversión',
        self::GUIAS_COLONIA          => 'Guías de colonia',
        self::PROCESO_VENTA          => 'Proceso de venta',
    ];

    private const BY_CATEGORY = [
        'herencias-y-sucesiones'    => self::HERENCIAS,
        'zonificacion-desarrollo'   => self::TERRENO_DESARROLLADORA,
        'mercado-inmobiliario-cdmx' => self::PRECIOS_INVERSION,
        'inversion-inmobiliaria'    => self::PRECIOS_INVERSION,
        'colonias-de-benito-juarez' => self::GUIAS_COLONIA,
        'vender-tu-propiedad'       => self::PROCESO_VENTA,
    ];

    /** Posts de herencias donde el slug ya delata que el lector decidió vender (Fase 3.6 del prompt). */
    private const DECIDED_SLUG_PATTERN = '/^vender-|isr-venta|hermano-no-quiere-vender/i';

    public static function forPost(?Post $post): ?string
    {
        if (! $post) {
            return null;
        }

        if (! empty($post->cluster) && array_key_exists($post->cluster, self::LABELS)) {
            return $post->cluster;
        }

        // El slug manda sobre la categoría para herencias: hay posts de herencias categorizados
        // distinto antes de que existiera "herencias-y-sucesiones".
        if (preg_match('/hered|sucesion|testamento/i', $post->slug ?? '')) {
            return self::HERENCIAS;
        }

        return self::BY_CATEGORY[$post->category?->slug] ?? null;
    }

    /**
     * ¿El lector de ESTE post de herencias ya decidió vender? Solo aplica al cluster herencias — el
     * CTA de "vender" se calla en los posts todavía informativos (cuánto cuesta la sucesión, cómo
     * regularizar) y habla en los que ya asumen la decisión tomada.
     */
    public static function showsSellCta(?Post $post): bool
    {
        if (! $post || self::forPost($post) !== self::HERENCIAS) {
            return false;
        }

        return (bool) preg_match(self::DECIDED_SLUG_PATTERN, $post->slug ?? '');
    }
}
