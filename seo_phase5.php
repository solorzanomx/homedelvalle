<?php
/**
 * SEO Phase 5 — Actualizar slugs 2025→2026
 * Ejecutar: php artisan tinker --execute="require base_path('seo_phase5.php');"
 */

use App\Models\Post;

$separator = str_repeat('─', 60);
echo "\nSEO Phase 5 — Slugs 2025→2026\n";
echo $separator . "\n\n";

$updates = [
    'invertir-en-narvarte-2025-guia-completa'
        => 'invertir-en-narvarte-2026-guia-completa',
    'invertir-inmuebles-benito-juarez-2025'
        => 'invertir-inmuebles-benito-juarez-2026',
    'invertir-en-napoles-o-acacias-benito-juarez-2025'
        => 'invertir-en-napoles-o-acacias-benito-juarez-2026',
    'como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2025'
        => 'como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026',
];

foreach ($updates as $oldSlug => $newSlug) {
    $post = Post::where('slug', $oldSlug)->first();
    if (! $post) {
        echo "  ⚠  No encontrado: {$oldSlug}\n";
        continue;
    }
    $post->slug = $newSlug;
    $post->save();
    echo "  ✓  Post {$post->id}: {$oldSlug}\n       → {$newSlug}\n\n";
}

echo str_repeat('═', 60) . "\n";
echo "✅  Slugs actualizados\n";
echo str_repeat('═', 60) . "\n\n";
