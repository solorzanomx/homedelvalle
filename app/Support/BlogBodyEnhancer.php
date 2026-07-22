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
    public static function enhance(string $html, string $valuationCta = '', string $predioCta = '', string $postTitle = '', ?string $predioCtaAfterHeading = null): array
    {
        $html = self::stripImageSlots($html);
        $html = self::fixImages($html, $postTitle);
        $html = self::linkColonias($html);

        if ($valuationCta !== '') {
            $html = self::injectAfterFirstTable($html, $valuationCta);
        }

        [$first, $second] = self::splitAtMidHeading($html);

        if ($predioCta !== '') {
            $injected = false;

            // Posición dirigida (p.ej. "justo después de Ejemplo práctico"):
            // si el encabezado no existe en este post en particular, cae al
            // comportamiento de siempre — nunca debe quedar sin CTA.
            if ($predioCtaAfterHeading !== null) {
                if (self::hasHeading($second, $predioCtaAfterHeading)) {
                    $second = self::injectAfterHeadingSection($second, $predioCtaAfterHeading, $predioCta);
                    $injected = true;
                } elseif (self::hasHeading($first, $predioCtaAfterHeading)) {
                    $first = self::injectAfterHeadingSection($first, $predioCtaAfterHeading, $predioCta);
                    $injected = true;
                }
            }

            if (!$injected) {
                if ($second !== '') {
                    $second = self::injectBeforeLastHeading($second, $predioCta);
                } else {
                    $first .= $predioCta;
                }
            }
        }

        return ['first' => $first, 'second' => $second];
    }

    /**
     * Los cuerpos de post pueden traer marcadores editoriales
     * <div class="hdv-img-slot">…</div> que indican dónde va cada imagen y
     * qué foto poner — visibles al editar en el admin, pero NUNCA para el
     * público: si el editor aún no sube la imagen, el marcador se elimina
     * del render sin dejar rastro. Al subir la foto, se reemplaza el div
     * completo por el <img> en el editor.
     */
    public static function stripImageSlots(string $html): string
    {
        return preg_replace_callback('/<div class="hdv-img-slot">(.*?)<\/div>/is', function ($m) {
            // Si el editor insertó la imagen DENTRO del recuadro (en vez de
            // reemplazarlo completo — caso real: las imágenes desaparecían
            // del público junto con el marcador), se rescata el <img> y solo
            // se elimina el envoltorio con el texto de instrucciones.
            preg_match_all('/<img\b[^>]*>/i', $m[1], $imgs);
            return implode('', $imgs[0]);
        }, $html) ?? $html;
    }

    /**
     * Higiene SEO de las imágenes del cuerpo (los posts generados por AI
     * traen defectos ya vistos en producción):
     *  - src con el typo de dominio "homedelvalle.mx.com" → dominio real
     *    (defensa en render; también hay migración de datos que lo corrige
     *    de raíz en la BD).
     *  - alt vacío o ausente → título del post (imperfecto pero muchísimo
     *    mejor que vacío para Google Imágenes y accesibilidad).
     *  - loading="lazy" si falta — todas las imágenes del cuerpo están
     *    debajo del fold.
     */
    public static function fixImages(string $html, string $postTitle = ''): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) use ($postTitle) {
            $img = str_replace('homedelvalle.mx.com', 'homedelvalle.mx', $m[0]);

            if ($postTitle !== '') {
                $alt = e($postTitle);
                if (preg_match('/\balt=(""|\'\')/', $img)) {
                    $img = preg_replace('/\balt=(""|\'\')/', 'alt="' . $alt . '"', $img, 1);
                } elseif (!preg_match('/\balt=/i', $img)) {
                    $img = preg_replace('/^<img\b/i', '<img alt="' . $alt . '"', $img, 1);
                }
            }

            if (!preg_match('/\bloading=/i', $img)) {
                $img = preg_replace('/^<img\b/i', '<img loading="lazy"', $img, 1);
            }

            return $img;
        }, $html) ?? $html;
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

    /** ¿El encabezado (h2/h3, match parcial insensible a mayúsculas) existe en este HTML? */
    public static function hasHeading(string $html, string $headingText): bool
    {
        return (bool) preg_match('/<h[23]\b[^>]*>[^<]*' . preg_quote($headingText, '/') . '/iu', $html);
    }

    /**
     * Inyecta $block justo al terminar la sección de $headingText (antes del
     * siguiente h2/h3, o al final del HTML si esa sección era la última).
     */
    public static function injectAfterHeadingSection(string $html, string $headingText, string $block): string
    {
        if (!preg_match('/<h[23]\b[^>]*>[^<]*' . preg_quote($headingText, '/') . '[^<]*<\/h[23]>/iu', $html, $m, PREG_OFFSET_CAPTURE)) {
            return $html . $block;
        }

        $headingEnd = $m[0][1] + strlen($m[0][0]);

        if (preg_match('/<h[23]\b/i', $html, $next, PREG_OFFSET_CAPTURE, $headingEnd)) {
            $cut = $next[0][1];
            return substr($html, 0, $cut) . $block . substr($html, $cut);
        }

        return $html . $block;
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
