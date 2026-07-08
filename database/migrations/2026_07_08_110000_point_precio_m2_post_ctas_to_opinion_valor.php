<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El post con más tráfico orgánico ("Precio por m² en BJ por colonia 2026")
 * no convertía: su CTA principal mandaba a /contacto (formulario genérico,
 * sin relación con valuación) en vez de a la Opinión de Valor que ya existe
 * exactamente para esa intención. Se actualizan destino y botón de ambos
 * CTAs preservando el resto de su contenido (títulos/descripciones son
 * editoriales, viven solo en BD). Defensiva: no-op si el post no existe.
 */
return new class extends Migration
{
    private const SLUG = 'precio-metro-cuadrado-colonias-benito-juarez-2026';

    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        $post = DB::table('posts')->where('slug', self::SLUG)->first();
        if (! $post || empty($post->ctas)) {
            return;
        }

        $ctas = json_decode($post->ctas, true);
        if (! is_array($ctas)) {
            return;
        }

        // CTA1 (~38% del artículo, tras la tabla): de /contacto genérico a
        // la Opinión de Valor — el formulario construido para esta intención.
        if (isset($ctas[0])) {
            $ctas[0]['link'] = '/precios/opinion-de-valor';
            $ctas[0]['button_text'] = 'Recibir mi opinión de valor gratuita';
        }

        // CTA2 (cierre): mismo destino (/vende-tu-propiedad), botón más
        // concreto que "Contactar a un especialista".
        if (isset($ctas[1])) {
            $ctas[1]['link'] = '/vende-tu-propiedad';
            $ctas[1]['button_text'] = 'Solicitar valuación gratuita';
        }

        DB::table('posts')->where('id', $post->id)->update([
            'ctas' => json_encode($ctas, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        $post = DB::table('posts')->where('slug', self::SLUG)->first();
        if (! $post || empty($post->ctas)) {
            return;
        }

        $ctas = json_decode($post->ctas, true);
        if (! is_array($ctas)) {
            return;
        }

        if (isset($ctas[0])) {
            $ctas[0]['link'] = '/contacto';
            $ctas[0]['button_text'] = 'Solicitar revisión de precio';
        }
        if (isset($ctas[1])) {
            $ctas[1]['link'] = '/vende-tu-propiedad';
            $ctas[1]['button_text'] = 'Contactar a un especialista';
        }

        DB::table('posts')->where('id', $post->id)->update([
            'ctas' => json_encode($ctas, JSON_UNESCAPED_UNICODE),
        ]);
    }
};
