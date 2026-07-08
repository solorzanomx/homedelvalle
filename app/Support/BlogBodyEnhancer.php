<?php

namespace App\Support;

use App\Models\MarketColonia;
use Illuminate\Support\Facades\Cache;

/**
 * Post-procesa el HTML ya renderizado de un post del blog para convertir
 * tráfico informacional en leads, sin tocar el contenido guardado en BD:
 *
 *  1. Nombres de colonias dentro de <table> se vuelven links a su página
 *     del Observatorio de Precios (/precios/{zona}/{colonia}).
 *  2. Un CTA de opinión de valor se inyecta justo después de la primera
 *     tabla — el momento de mayor intención ("ya vi el promedio, ¿y MI
 *     propiedad?").
 *  3. Un banner predio→desarrolladora se inyecta antes del último <h2>
 *     (la sección de cierre, donde cae el "tu casa puede valer más").
 *  4. El cuerpo se parte a media lectura para embeber un formulario
 *     Livewire entre ambas mitades (Livewire no puede vivir dentro de
 *     HTML crudo de BD, por eso el split en vez de inyección).
 *
 * Diseñado defensivo: si un anclaje no existe (sin tablas, pocos h2),
 * ese paso se omite y el resto sigue funcionando.
 */
class BlogBodyEnhancer
{
    /**
     * @return array{first: string, second: string} — 'second' vacío si no
     *         hubo dónde partir (el form se muestra al final igualmente).
     */
    public static function enhance(string $html, string $valuationCta = '', string $predioCta = ''): array
    {
        $html = self::linkColonias($html);

        if ($valuationCta !== '') {
            $html = self::injectAfterFirstTable($html, $valuationCta);
        }

        [$first, $second] = self::splitAtMidHeading($html);

        if ($predioCta !== '') {
            if ($second !== '') {
                $second = self::injectBeforeLastHeading($second, $predioCta);
            } else {
                $first .= $predioCta;
            }
        }

        return ['first' => $first, 'second' => $second];
    }

    /**
     * Dentro de tablas, enlaza nombres de colonias publicadas a su página
     * del Observatorio. Nombres más largos primero para que "Del Valle
     * Centro" no quede capturado a medias por "Del Valle".
     */
    public static function linkColonias(string $html): string
    {
        $colonias = Cache::remember('blog_enhancer_colonias', 3600, function () {
            return MarketColonia::query()
                ->where('is_published', true)
                ->with('zone:id,slug')
                ->get(['id', 'name', 'slug', 'market_zone_id'])
                ->filter(fn ($c) => $c->zone?->slug)
                ->map(fn ($c) => [
                    'name' => $c->name,
                    'url'  => route('precios.colonia', [$c->zone->slug, $c->slug]),
                ])
                ->sortByDesc(fn ($c) => mb_strlen($c['name']))
                ->values()
                ->all();
        });

        if (empty($colonias)) {
            return $html;
        }

        return preg_replace_callback('/<table\b.*?<\/table>/is', function ($m) use ($colonias) {
            $table = $m[0];
            foreach ($colonias as $colonia) {
                $name = preg_quote($colonia['name'], '/');
                // Solo texto suelto en celdas — nunca dentro de un <a> ya
                // existente ni partiendo un atributo. Límites Unicode
                // explícitos: \b falla con nombres que inician con acento
                // ("Álamos") porque \w es ASCII por defecto en PCRE.
                $table = preg_replace(
                    '/(?<=>)([^<>]*?)(?<![\pL\pN])(' . $name . ')(?![\pL\pN])/iu',
                    '$1<a href="' . $colonia['url'] . '" class="text-brand-600 font-semibold no-underline border-b border-brand-200 hover:border-brand-500">$2</a>',
                    $table,
                    1
                );
            }
            return $table;
        }, $html) ?? $html;
    }

    public static function injectAfterFirstTable(string $html, string $block): string
    {
        $pos = stripos($html, '</table>');
        if ($pos === false) {
            return $html;
        }
        $end = $pos + strlen('</table>');

        return substr($html, 0, $end) . $block . substr($html, $end);
    }

    /**
     * Parte el HTML justo antes del <h2> más cercano a la mitad de la
     * lectura (excluyendo el primero — partir ahí dejaría la primera
     * mitad casi vacía). Los cuerpos del blog son HTML plano (secuencia
     * de h2/p/table al nivel raíz), así que cortar en un límite de <h2>
     * mantiene ambas mitades bien formadas.
     */
    public static function splitAtMidHeading(string $html): array
    {
        preg_match_all('/<h2\b/i', $html, $matches, PREG_OFFSET_CAPTURE);
        $positions = array_column($matches[0], 1);

        if (count($positions) < 3) {
            return [$html, ''];
        }

        array_shift($positions); // nunca partir en el primer h2

        $target = (int) (strlen($html) * 0.55);
        usort($positions, fn ($a, $b) => abs($a - $target) <=> abs($b - $target));
        $cut = $positions[0];

        return [substr($html, 0, $cut), substr($html, $cut)];
    }

    public static function injectBeforeLastHeading(string $html, string $block): string
    {
        preg_match_all('/<h2\b/i', $html, $matches, PREG_OFFSET_CAPTURE);
        $positions = array_column($matches[0], 1);

        if (empty($positions)) {
            return $html . $block;
        }

        $cut = end($positions);

        return substr($html, 0, $cut) . $block . substr($html, $cut);
    }
}
