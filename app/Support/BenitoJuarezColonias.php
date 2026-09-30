<?php

namespace App\Support;

use App\Models\MarketZone;
use Illuminate\Support\Facades\Cache;

/**
 * Catálogo real de colonias de Benito Juárez, reusado del mismo MarketZone/MarketColonia que ya
 * usa el sistema de precios/valuación (Opinión de Valor) — no se inventa una lista aparte ni hay
 * que mantenerla en dos lugares.
 *
 * Se usa en los formularios del blog (SuccessionCalculator, CtaCapture) para que cada lead diga de
 * qué colonia es, y así se pueda saber de un vistazo si es un prospecto real de Benito Juárez o no
 * (hallazgo 2026-09-30: leads con teléfonos falsos y de fuera de la zona — ver
 * docs/funcionalidades/blog-optimizaciones-post-lanzamiento.md).
 */
class BenitoJuarezColonias
{
    public const FUERA_DE_BJ = 'Otra colonia (fuera de Benito Juárez)';

    /** @return array<string, array<int, string>>  Nombre de zona => nombres de colonia, alfabético dentro de cada zona. */
    public static function grouped(): array
    {
        return Cache::remember('blog_colonias_bj_grouped', now()->addHour(), function () {
            return MarketZone::published()
                ->with('publishedColonias')
                ->get()
                ->mapWithKeys(fn ($zone) => [$zone->name => $zone->publishedColonias->pluck('name')->all()])
                ->filter(fn ($colonias) => count($colonias) > 0)
                ->all();
        });
    }

    /** Todos los valores válidos para el select (colonias reales + el catch-all "fuera de BJ") — usado en la regla `in:`. */
    public static function validValues(): array
    {
        $flat = [];
        foreach (self::grouped() as $colonias) {
            $flat = [...$flat, ...$colonias];
        }
        $flat[] = self::FUERA_DE_BJ;

        return $flat;
    }
}
