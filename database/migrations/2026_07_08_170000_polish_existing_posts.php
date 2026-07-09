<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Última pasada a los posts existentes (auditoría de los 16 en producción,
 * 2026-07-08). Dos fixes quirúrgicos:
 *
 * 1. El post de H5/H6 usa <h3><strong>…</strong></h3> como títulos de
 *    sección en vez de <h2> — jerarquía SEO incorrecta y, peor, los
 *    módulos de conversión (mini-form a media lectura, banner predio)
 *    se anclan en h2: con cero h2, ese post clave del funnel no recibía
 *    NINGUNO. Se convierten a <h2> (excepto el h3 que envuelve una
 *    imagen).
 *
 * 2. El CTA del post "¿Tu casa vale más como terreno?" apuntaba a
 *    /contacto genérico — el destino correcto es la landing del funnel.
 *
 * Defensiva: no-op donde el post no exista (local sin posts).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        // 1. h3→h2 en el post de H5/H6.
        $post = DB::table('posts')
            ->where('slug', 'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo')
            ->first();
        if ($post && $post->body) {
            $body = preg_replace(
                '/<h3([^>]*)><strong>(?!<img)(.*?)<\/strong><\/h3>/is',
                '<h2$1>$2</h2>',
                $post->body
            );
            if ($body && $body !== $post->body) {
                DB::table('posts')->where('id', $post->id)->update(['body' => $body, 'updated_at' => now()]);
            }
        }

        // 2. CTA del post de terreno → landing del funnel.
        $post = DB::table('posts')
            ->where('slug', 'vender-casa-terreno-constructores-benito-juarez-2026')
            ->first();
        if ($post && ! empty($post->ctas)) {
            $ctas = json_decode($post->ctas, true);
            if (is_array($ctas) && isset($ctas[0])) {
                $ctas[0]['link'] = '/vende-a-desarrolladora';
                $ctas[0]['button_text'] = 'Evaluar mi propiedad como terreno';
                DB::table('posts')->where('id', $post->id)->update([
                    'ctas' => json_encode($ctas, JSON_UNESCAPED_UNICODE),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Correcciones editoriales — sin reversa automática.
    }
};
