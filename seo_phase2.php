<?php
/**
 * SEO Phase 2 — Home del Valle Blog
 * Ejecutar con: php artisan tinker --execute="require base_path('seo_phase2.php');"
 *
 * Acciones:
 *  1. Reemplaza placeholders (LINK INTERNO: ...) con <a href> reales
 *  2. Corrige CTAs con link vacío (posts 8, 11, 12)
 *  3. Configura CTAs para post 3 (todos estaban vacíos)
 *  4. Guarda internal_links JSON en posts que reciben links
 *  5. Corrige año 2025→2026 en secciones FAQ/tendencias (post 1, 16)
 */

use App\Models\Post;

$separator = str_repeat('─', 60);
echo "\nSEO Phase 2 — Home del Valle Blog\n";
echo $separator . "\n\n";

// ============================================================
// 1. LINK INTERNO REPLACEMENTS
// Map: post_id => [ [pattern, replacement], ... ]
// ============================================================
echo "1. Reemplazando placeholders (LINK INTERNO: ...)\n";
echo $separator . "\n";

$linkMap = [

    // Post 4: Zonificación H5/H6
    // Contexto: ciclo de valor, negociación de precio
    // El artículo de "negociación" no existe → link a precio por colonia
    4 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/precio-metro-cuadrado-colonias-benito-juarez-2026">'
                       . 'conoce el precio real por colonia en Benito Juárez</a>',
        ],
    ],

    // Post 5: Herencia BJ — dos placeholders distintos
    // 1° → sin testamento CDMX (ID 6)
    // 2° → precio por colonia BJ (ID 11)
    5 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]*sin testamento[^)]*\)/ui',
            'replace' => '<a href="/blog/propiedad-sin-testamento-cdmx-como-regularizar-vender-2026">'
                       . 'regularizar una propiedad sin testamento en CDMX</a>',
        ],
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',   // atrapa el restante
            'replace' => '<a href="/blog/precio-metro-cuadrado-colonias-benito-juarez-2026">'
                       . 'cuánto vale tu propiedad en Benito Juárez por colonia</a>',
        ],
    ],

    // Post 6: Sin testamento CDMX → precio por colonia (ID 11)
    6 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/precio-metro-cuadrado-colonias-benito-juarez-2026">'
                       . 'cuánto vale tu propiedad en Benito Juárez</a>',
        ],
    ],

    // Post 7: ISR herencia → cómo vender heredada BJ (ID 5)
    7 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/vender-propiedad-heredada-benito-juarez-2026">'
                       . 'cómo vender una propiedad heredada en Benito Juárez</a>',
        ],
    ],

    // Post 8: Coheredero → precio por colonia (ID 11)
    8 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/precio-metro-cuadrado-colonias-benito-juarez-2026">'
                       . 'cuánto vale tu propiedad en Benito Juárez por colonia</a>',
        ],
    ],

    // Post 9: Inquilinos → precio por colonia (ID 11)
    9 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/precio-metro-cuadrado-colonias-benito-juarez-2026">'
                       . 'cuánto vale tu propiedad en Benito Juárez por colonia</a>',
        ],
    ],

    // Post 10: Vender o rentar → artículo terreno/constructores (ID 3)
    10 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar">'
                       . 'cuánto pagan los constructores por terrenos en Benito Juárez</a>',
        ],
    ],

    // Post 11: Precio por colonia → artículo terreno/constructores (ID 3)
    11 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar">'
                       . 'qué pagan los constructores por terrenos en Benito Juárez</a>',
        ],
    ],

    // Post 12: Terreno constructores → precio por colonia (ID 11)
    12 => [
        [
            'pattern' => '/\(LINK INTERNO:[^)]+\)/ui',
            'replace' => '<a href="/blog/precio-metro-cuadrado-colonias-benito-juarez-2026">'
                       . 'precio por m² en Benito Juárez por colonia</a>',
        ],
    ],
];

foreach ($linkMap as $postId => $replacements) {
    $post = Post::find($postId);
    if (! $post) {
        echo "  ⚠  Post ID {$postId} no encontrado\n";
        continue;
    }

    $body = $post->body;
    $before = preg_match_all('/\(LINK INTERNO:[^)]+\)/ui', $body);

    foreach ($replacements as $rep) {
        $body = preg_replace($rep['pattern'], $rep['replace'], $body);
    }

    $after = preg_match_all('/\(LINK INTERNO:[^)]+\)/ui', $body);
    $fixed = $before - $after;

    $post->body = $body;
    $post->save();

    $status = $after > 0 ? "⚠  {$after} placeholder(s) sin resolver" : "✓";
    echo "  {$status}  Post {$postId} ({$post->slug}): {$fixed}/{$before} reemplazados\n";
}

echo "\n";

// ============================================================
// 2. CORREGIR CTA LINKS VACÍOS (Posts 8, 11, 12)
// ============================================================
echo "2. Corrigiendo links vacíos en CTAs\n";
echo $separator . "\n";

$ctaLinkFixes = [
    8  => ['/contacto',          '/vende-tu-propiedad'],
    11 => ['/contacto',          '/vende-tu-propiedad'],
    12 => ['/contacto',          '/vende-tu-propiedad'],
];

foreach ($ctaLinkFixes as $postId => $links) {
    $post = Post::find($postId);
    if (! $post) continue;

    $ctas = $post->ctas ?? [];

    foreach ($links as $idx => $url) {
        if (isset($ctas[$idx]) && empty($ctas[$idx]['link'])) {
            $ctas[$idx]['link'] = $url;
        }
    }

    $post->ctas = $ctas;
    $post->save();
    echo "  ✓  Post {$postId}: links CTA[0]={$links[0]}, CTA[1]={$links[1]}\n";
}

echo "\n";

// ============================================================
// 3. CONFIGURAR CTAs PARA POST 3 (todos vacíos, sin placeholders)
// ============================================================
echo "3. Configurando CTAs para Post 3 (terreno desarrollable BJ)\n";
echo $separator . "\n";

$post3 = Post::find(3);
if ($post3) {
    $post3->ctas = [
        [
            'title'       => '¿Cuánto vale realmente tu propiedad en Benito Juárez?',
            'description' => 'Solicita una valuación gratuita y descubre su valor real como terreno '
                           . 'desarrollable. Sin compromiso, en 24 horas.',
            'button_text' => 'Solicitar valuación gratuita',
            'link'        => '/vende-tu-propiedad',
        ],
        [
            'title'       => 'Habla con un especialista en terrenos de desarrollo',
            'description' => 'Llevamos 30+ años conectando propietarios en Benito Juárez con '
                           . 'constructoras. Te ayudamos a obtener el mejor precio.',
            'button_text' => 'Contactar especialista',
            'link'        => '/contacto',
        ],
        [
            'title'       => '',
            'description' => '',
            'button_text' => '',
            'link'        => '',
        ],
    ];
    $post3->save();
    echo "  ✓  Post 3: 2 CTAs configurados\n";
    echo "  ⚠  PENDIENTE MANUAL: agregar {{CTA1}} y {{CTA2}} en el cuerpo del artículo\n";
    echo "     CTA1 → después del primer H2\n";
    echo "     CTA2 → antes del cierre\n";
}

echo "\n";

// ============================================================
// 4. GUARDAR internal_links JSON
//    Solo para posts donde se añadieron links reales al cuerpo
// ============================================================
echo "4. Guardando internal_links en base de datos\n";
echo $separator . "\n";

$internalLinksData = [
    4  => [
        ['anchor' => 'precio real por colonia en Benito Juárez',
         'url'    => '/blog/precio-metro-cuadrado-colonias-benito-juarez-2026'],
    ],
    5  => [
        ['anchor' => 'regularizar una propiedad sin testamento en CDMX',
         'url'    => '/blog/propiedad-sin-testamento-cdmx-como-regularizar-vender-2026'],
        ['anchor' => 'cuánto vale tu propiedad en Benito Juárez por colonia',
         'url'    => '/blog/precio-metro-cuadrado-colonias-benito-juarez-2026'],
    ],
    6  => [
        ['anchor' => 'cuánto vale tu propiedad en Benito Juárez',
         'url'    => '/blog/precio-metro-cuadrado-colonias-benito-juarez-2026'],
    ],
    7  => [
        ['anchor' => 'cómo vender una propiedad heredada en Benito Juárez',
         'url'    => '/blog/vender-propiedad-heredada-benito-juarez-2026'],
    ],
    8  => [
        ['anchor' => 'cuánto vale tu propiedad en Benito Juárez por colonia',
         'url'    => '/blog/precio-metro-cuadrado-colonias-benito-juarez-2026'],
    ],
    9  => [
        ['anchor' => 'cuánto vale tu propiedad en Benito Juárez por colonia',
         'url'    => '/blog/precio-metro-cuadrado-colonias-benito-juarez-2026'],
    ],
    10 => [
        ['anchor' => 'cuánto pagan los constructores por terrenos en Benito Juárez',
         'url'    => '/blog/como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar'],
    ],
    11 => [
        ['anchor' => 'qué pagan los constructores por terrenos en Benito Juárez',
         'url'    => '/blog/como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar'],
    ],
    12 => [
        ['anchor' => 'precio por m² en Benito Juárez por colonia',
         'url'    => '/blog/precio-metro-cuadrado-colonias-benito-juarez-2026'],
    ],
];

foreach ($internalLinksData as $postId => $links) {
    $post = Post::find($postId);
    if (! $post) continue;

    $existing = $post->internal_links ?? [];
    // Merge sin duplicar URLs
    $existingUrls = array_column($existing, 'url');
    foreach ($links as $link) {
        if (! in_array($link['url'], $existingUrls)) {
            $existing[] = $link;
        }
    }

    $post->internal_links = $existing;
    $post->save();
    echo "  ✓  Post {$postId}: " . count($links) . " link(s) guardado(s)\n";
}

echo "\n";

// ============================================================
// 5. CORREGIR AÑO 2025→2026 EN SECCIONES FAQ / TENDENCIAS
//    Solo reemplazos seguros (títulos de sección, FAQ headers)
//    No tocar datos históricos de mercado
// ============================================================
echo "5. Corrigiendo año en secciones FAQ y tendencias\n";
echo $separator . "\n";

// Post 1: "Tendencias 2025" en un header de sección
$post1 = Post::find(1);
if ($post1) {
    $body = $post1->body;
    $body = str_replace('Tendencias 2025:', 'Tendencias 2026:', $body);
    $body = str_replace('Tendencias 2025\r\n', 'Tendencias 2026\r\n', $body);
    $body = str_replace('>Tendencias 2025<', '>Tendencias 2026<', $body);
    $post1->body = $body;
    $post1->save();
    echo "  ✓  Post 1: \"Tendencias 2025\" → \"Tendencias 2026\"\n";
}

// Post 16: FAQ headers que preguntan "en 2025?" — contexto de año vigente
$post16 = Post::find(16);
if ($post16) {
    $body  = $post16->body;
    $fixes = [
        'Preguntas frecuentes sobre invertir en Narvarte en 2025'
            => 'Preguntas frecuentes sobre invertir en Narvarte en 2026',
        '¿Cuánto cuesta un departamento en Narvarte en 2025?'
            => '¿Cuánto cuesta un departamento en Narvarte en 2026?',
        '¿Es mejor invertir en Narvarte en 2025'
            => '¿Es mejor invertir en Narvarte en 2026',
        'invertir en Narvarte en 2025?'
            => 'invertir en Narvarte en 2026?',
    ];
    foreach ($fixes as $find => $replace) {
        $body = str_replace($find, $replace, $body);
    }
    $post16->body = $body;
    $post16->save();
    echo "  ✓  Post 16: FAQ \"2025\" → \"2026\" (datos históricos intactos)\n";
}

// ============================================================
// RESUMEN FINAL
// ============================================================
echo "\n";
echo str_repeat('═', 60) . "\n";
echo "✅  SEO Phase 2 completado\n";
echo str_repeat('═', 60) . "\n\n";

echo "PENDIENTES MANUALES (requieren edición de contenido):\n\n";
echo "  1. Post 3 (terreno BJ) — agregar en el admin:\n";
echo "     - {{CTA1}} después del primer H2\n";
echo "     - {{CTA2}} antes del párrafo final\n\n";
echo "  2. Posts 16, 18, 19 — revisar tablas de precios:\n";
echo "     - Datos históricos de Q3/Q4 2025 son correctos\n";
echo "     - Solo actualizar si dicen 'el precio ACTUAL en 2025'\n\n";
echo "  3. Post 4 — verificar que el nuevo link queda natural en contexto\n\n";
echo "  4. Ejecutar Phase 1 SQL (si no se ha hecho):\n";
echo "     mysql -u USER -p DB < seo_phase1.sql\n\n";

echo "PRÓXIMA MEJORA RECOMENDADA:\n";
echo "  - Agregar FAQPage schema en Posts 7, 15, 16 (tienen sección P:/R:)\n";
echo "  - Publicar Post 12 (draft, tema de alto valor)\n";
echo "  - Crear artículo: 'Guía de negociación — cómo obtener el mejor precio'\n";
echo "  - Crear artículo comprador: 'Cómo comprar departamento en Benito Juárez'\n\n";
