<?php
/**
 * SEO Phase 6 — Corregir año contextual 2025→2026 en posts 16, 18, 19
 * Conserva datos históricos (trimestres, estadísticas puntuales)
 * Ejecutar: php artisan tinker --execute="require base_path('seo_phase6.php');"
 */

use App\Models\Post;

$separator = str_repeat('─', 60);
echo "\nSEO Phase 6 — Año contextual 2025→2026\n";
echo $separator . "\n\n";

// ── POST 16: Narvarte ────────────────────────────────────────
$post16 = Post::find(16);
if ($post16) {
    $body = $post16->body;
    $fixes = [
        // Contextual — cambiar
        'en 2025 esa tendencia no muestra'      => 'en 2026 esa tendencia no muestra',
        'proyecci&oacute;n en 2025'             => 'proyecci&oacute;n en 2026',
        'En 2025, las tasas en M&eacute;xico'   => 'En 2026, las tasas en M&eacute;xico',
        // Plain text variants
        'proyección en 2025'                    => 'proyección en 2026',
        'En 2025, las tasas en México'          => 'En 2026, las tasas en México',
    ];
    $count = 0;
    foreach ($fixes as $find => $replace) {
        $new = str_replace($find, $replace, $body);
        if ($new !== $body) { $count++; $body = $new; }
    }
    $post16->body = $body;
    $post16->save();
    echo "  ✓  Post 16 (Narvarte): {$count} reemplazos\n";
}

// ── POST 18: Nápoles/Acacias ─────────────────────────────────
$post18 = Post::find(18);
if ($post18) {
    $body = $post18->body;
    $fixes = [
        // Contextual — cambiar
        'decisi&oacute;n de inversi&oacute;n en 2025'          => 'decisi&oacute;n de inversi&oacute;n en 2026',
        'oportunidades reales y documentadas en 2025'          => 'oportunidades reales y documentadas en 2026',
        // Plain text
        'decisión de inversión en 2025'                        => 'decisión de inversión en 2026',
        'oportunidades reales y documentadas en 2025'          => 'oportunidades reales y documentadas en 2026',
    ];
    $count = 0;
    foreach ($fixes as $find => $replace) {
        $new = str_replace($find, $replace, $body);
        if ($new !== $body) { $count++; $body = $new; }
    }
    $post18->body = $body;
    $post18->save();
    echo "  ✓  Post 18 (Nápoles/Acacias): {$count} reemplazos\n";
}

// ── POST 19: Invertir en BJ ──────────────────────────────────
$post19 = Post::find(19);
if ($post19) {
    $body = $post19->body;
    $fixes = [
        // Contextual — cambiar
        'de un departamento en CDMX en 2025 depende'           => 'de un departamento en CDMX en 2026 depende',
        'al cierre de 2025, el mercado de arrendamiento'       => 'al cierre de 2026, el mercado de arrendamiento',
        // Plain text
        'de un departamento en CDMX en 2025 depende'           => 'de un departamento en CDMX en 2026 depende',
        'al cierre de 2025, el mercado'                        => 'al cierre de 2026, el mercado',
    ];
    $count = 0;
    foreach ($fixes as $find => $replace) {
        $new = str_replace($find, $replace, $body);
        if ($new !== $body) { $count++; $body = $new; }
    }
    $post19->body = $body;
    $post19->save();
    echo "  ✓  Post 19 (Invertir BJ): {$count} reemplazos\n";
}

echo "\n" . str_repeat('═', 60) . "\n";
echo "✅  Phase 6 completado\n";
echo "    Datos históricos (trimestres, estadísticas) conservados\n";
echo str_repeat('═', 60) . "\n\n";
