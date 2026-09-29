<?php

namespace App\Support;

use App\Models\Post;

/**
 * En qué etapa del funnel está el lector de un post (Fase 3 del prompt de leads del blog:
 * docs/funcionalidades/blog-cta-clusters.md). Fuente única de verdad — antes el heurístico de
 * "es herencias" vivía duplicado a mano dentro de `blog/_cta-predio.blade.php`.
 *
 * Deliberadamente NO es una columna de `posts` todavía: se deriva de `category` (que ya existe y
 * ya enruta el CTA automático) más un ajuste por slug para herencias. Si el negocio pide poder
 * editarlo por post sin depender de la categoría, ahí sí se vuelve columna — de momento evita una
 * migración y una fuente de verdad extra que se pueda desalinear de la categoría real.
 */
class BlogCluster
{
    public const HERENCIAS = 'herencias';
    public const TERRENO_DESARROLLADORA = 'terreno_desarrolladora';
    public const PRECIOS_INVERSION = 'precios_inversion';
    public const GUIAS_COLONIA = 'guias_colonia';
    public const PROCESO_VENTA = 'proceso_venta';

    private const BY_CATEGORY = [
        'herencias-y-sucesiones'    => self::HERENCIAS,
        'zonificacion-desarrollo'   => self::TERRENO_DESARROLLADORA,
        'mercado-inmobiliario-cdmx' => self::PRECIOS_INVERSION,
        'inversion-inmobiliaria'    => self::PRECIOS_INVERSION,
        'colonias-de-benito-juarez' => self::GUIAS_COLONIA,
        'vender-tu-propiedad'       => self::PROCESO_VENTA,
    ];

    public static function forPost(?Post $post): ?string
    {
        if (! $post) {
            return null;
        }

        // El slug manda sobre la categoría para herencias: hay posts de herencias categorizados
        // distinto antes de que existiera "herencias-y-sucesiones" (mismo criterio que ya usaba
        // _cta-predio.blade.php).
        if (preg_match('/hered|sucesion|testamento/i', $post->slug ?? '')) {
            return self::HERENCIAS;
        }

        return self::BY_CATEGORY[$post->category?->slug] ?? null;
    }
}
