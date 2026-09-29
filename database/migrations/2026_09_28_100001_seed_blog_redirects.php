<?php

use App\Models\BlogRedirect;
use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

/**
 * Siembra de la Fase 1 (prompt-claude-code-blog-leads.md): slugs fantasma indexados por Google.
 * Cada destino se verifica ANTES de insertar — si el post destino no existe todavía (p. ej. local,
 * donde el catálogo real no vive), el redirect simplemente no se crea; producción sí los tendrá.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['/blog/cuanto-sucesion-cdmx-2026', '/blog/cuanto-cuesta-sucesion-cdmx-2026'],
            ['/blog/cuestan-sucesiones-cdmx-2026', '/blog/cuanto-cuesta-sucesion-cdmx-2026'],
            ['/blog/hermano-no-quiere-vender-la-propiedad-heredada-opciones-legales-cdmx', '/blog/hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx'],
            ['/blog/hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmv', '/blog/hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx'],
            ['/blog/invertir-narvarte-2026-guia-completa', '/blog/invertir-en-narvarte-2026-guia-completa'],
            ['/blog/plusvalia-benito-juarez-plusvalia-por-colonia', '/blog/plusvalia-benito-juarez-por-colonia'],
            ['/blog/usufructo-vitalicio-que-paso-al-morir-benito-juarez', '/blog/usufructo-vitalicio-que-pasa-al-morir-benito-juarez'],
            ['/blog/vender-casa-constructora-proceso-tiemp-cdmx', '/blog/vender-casa-constructora-proceso-tiempos-cdmx'],
            ['/blog/vivir-en-del-valle-pros-contras-2026', '/blog/vivir-en-del-valle-precios-pros-contras-2026'],
            ['/blog/vivir-en-del-valle-sur-cdmx-diferencias-centro-norte', '/blog/vivir-en-del-valle-sur-cdmx-diferencias-centro-norte'],
            ['/blog/vivir-en-del-valle-sur-diferencias-centro-norte', '/blog/vivir-en-del-valle-sur-cdmx-diferencias-centro-norte'],
            ['/blog/vivir-en-napoles-cdmx-pros-contras-2026', '/blog/vivir-en-napoles-cdmx-precios-pros-contras-2026'],
            ['/blog/vivir-en-narvarte-precios-contras-2026', '/blog/vivir-en-narvarte-precios-pros-contras-2026'],
            ['/blog/vivir-en-narvarte-pros-contras-2026', '/blog/vivir-en-narvarte-precios-pros-contras-2026'],
            ['/blog/vivir-narvarte-precios-pros-contras-2026', '/blog/vivir-en-narvarte-precios-pros-contras-2026'],
            ['/blog/vivir-en-portales-pros-contras', '/blog/vivir-en-portales-precios-pros-contras-2026'],
            ['/blog/vivir-en-portales-pros-contras-2026', '/blog/vivir-en-portales-precios-pros-contras-2026'],
        ];

        foreach ($rows as [$from, $to]) {
            $from = BlogRedirect::normalize($from);
            $to = BlogRedirect::normalize($to);
            $targetSlug = ltrim(str_replace('/blog/', '', $to), '/');

            if (! Post::where('slug', $targetSlug)->exists()) {
                continue;   // el destino no existe todavía aquí (local) — no se inventa un redirect a la nada
            }

            BlogRedirect::updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status' => 301, 'active' => true, 'notes' => 'seed inicial']);
        }

        // Caso condicional: solo redirige si el post viejo NO existe (si existe, no se toca).
        $legacySlug = 'por-que-comprar-casa-en-del-valle';
        $fallbackTarget = 'vivir-en-del-valle-precios-pros-contras-2026';
        if (! Post::where('slug', $legacySlug)->exists() && Post::where('slug', $fallbackTarget)->exists()) {
            BlogRedirect::updateOrCreate(
                ['from_path' => '/blog/' . $legacySlug],
                ['to_path' => '/blog/' . $fallbackTarget, 'status' => 301, 'active' => true, 'notes' => 'seed inicial (condicional)']
            );
        }
    }

    public function down(): void
    {
        BlogRedirect::where('notes', 'like', 'seed inicial%')->delete();
    }
};
