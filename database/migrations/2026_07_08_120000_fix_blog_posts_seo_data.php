<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Higiene SEO de los posts existentes (defectos encontrados auditando el
 * post con más tráfico orgánico, 2026-07-08). Todo defensivo/no-op donde
 * no aplique (local no tiene posts):
 *
 * 1. Typo de dominio "homedelvalle.mx.com" en src de imágenes del body —
 *    imágenes rotas en producción. Se corrige en TODOS los posts (el
 *    typo vino del generador AI, puede estar en varios).
 * 2. El post de precio por m² tiene la misma imagen dos veces (la del
 *    dominio roto es copia byte a byte de la siguiente con otro nombre de
 *    archivo) — se elimina el <img> duplicado.
 * 3. meta_title con sufijo " | HDV" — el componente seo-meta agrega
 *    "| Home del Valle", quedaba doble marca ("… | HDV | Home del Valle")
 *    desperdiciando caracteres del title en Google.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        // 2. Imagen duplicada del post de precios (antes del fix de dominio,
        //    identificada por nombre de archivo — funciona con cualquier host).
        $post = DB::table('posts')->where('slug', 'precio-metro-cuadrado-colonias-benito-juarez-2026')->first();
        if ($post && str_contains($post->body ?? '', 'cuanto-vale-mi-casa-o-departamento-1-1e88ec.webp')) {
            $body = preg_replace('/<img\b[^>]*cuanto-vale-mi-casa-o-departamento-1-1e88ec\.webp[^>]*>/i', '', $post->body);
            $body = preg_replace('/<p>\s*<\/p>/i', '', $body);
            DB::table('posts')->where('id', $post->id)->update(['body' => $body]);
        }

        // 1. Typo de dominio en todos los posts.
        DB::table('posts')
            ->where('body', 'like', '%homedelvalle.mx.com%')
            ->get(['id', 'body'])
            ->each(function ($p) {
                DB::table('posts')->where('id', $p->id)->update([
                    'body' => str_replace('homedelvalle.mx.com', 'homedelvalle.mx', $p->body),
                ]);
            });

        // 3. Sufijo " | HDV" en meta_title.
        DB::table('posts')
            ->where('meta_title', 'like', '%| HDV')
            ->get(['id', 'meta_title'])
            ->each(function ($p) {
                DB::table('posts')->where('id', $p->id)->update([
                    'meta_title' => rtrim(preg_replace('/\s*\|\s*HDV\s*$/', '', $p->meta_title)),
                ]);
            });
    }

    public function down(): void
    {
        // Correcciones de datos defectuosos (dominio roto, imagen duplicada,
        // doble marca) — no tiene sentido restaurar los defectos.
    }
};
