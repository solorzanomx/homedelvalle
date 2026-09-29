<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Auditoría de conversión 2026-09-28, parte 2: al revisar un post fusionado en vivo se encontró
 * que casi TODOS los posts publicados (58/59 con {{CTA1}}, 50/59 con {{CTA2}}, 41/59 con {{CTA3}})
 * todavía traen el sistema de CTAs de antes de la Fase 3 — 3 tarjetas estáticas ("Solicitar
 * asesoría" / "/contacto", sin captura real) repartidas por el cuerpo del post. `BlogAIService`
 * seguía instruyendo a la IA a insertarlas en CADA post nuevo (ver ese archivo, ya corregido en el
 * mismo commit). Con el sistema de CTAs por cluster (Fase 3) + calculadora/form a media lectura
 * (Fase 4) + CTA final garantizado (fix de esta misma auditoría), un post terminaba con 5-6 CTAs
 * apilados — el caso encontrado: como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026.
 *
 * `Post::getRenderedBodyAttribute()` resuelve `{{CTA1}}/{{CTA2}}/{{CTA3}}` leyendo
 * `$post->ctas[index-1]` — si esa entrada no tiene `title`, el shortcode se reemplaza por cadena
 * vacía (ver `getCta()`). Por eso esta migración NO toca el texto de `body` (el `{{CTAn}}` literal
 * puede quedarse ahí sin problema): solo vacía la columna `ctas` de todos los posts publicados, lo
 * que apaga los 3 shortcodes de golpe, sin riesgo de romper HTML ni dejar placeholders visibles.
 * El resto de los CTAs (link inline por cluster, form/calculadora a media lectura, CTA final,
 * predio→desarrolladora en herencias) no dependen de `ctas` — siguen intactos.
 *
 * No es reversible con datos: el contenido original de `ctas` (títulos/botones placeholder,
 * redundantes con los CTAs nuevos) no se respalda — no vale la pena, no es información que el
 * negocio necesite recuperar. Si algún post puntual sí quiere un CTA editorial extra, se edita a
 * mano desde /admin/posts/{id}/edit (el campo sigue funcionando, solo se vació el contenido viejo).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')
            ->where('status', 'published')
            ->update(['ctas' => '[]']);
    }

    public function down(): void
    {
        // No reversible con datos — ver comentario de cabecera.
    }
};
