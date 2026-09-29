<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 5.4 del prompt de leads del blog: cada post de herencias enlaza a los otros 3 "motores"
 * (los 4 artículos con más tráfico del cluster, según el reporte de GSC de Alejandro) y a la
 * calculadora. `cuanto-cuesta-sucesion-cdmx-2026` ya enlazaba a los otros 3 (verificado leyendo el
 * body real) y ya aloja la calculadora — no se toca. Inserción quirúrgica: un párrafo nuevo antes
 * del cierre de cada post, nunca se reescribe contenido existente. No-op si el post no existe
 * (local no tiene el catálogo real) o si el párrafo ancla ya no coincide (contenido editado desde
 * entonces) — mejor no insertar que insertar en el lugar equivocado.
 */
return new class extends Migration
{
    private const P_CLASS = 'font-claude-response-body break-words whitespace-normal leading-[1.7]';

    /** slug => [párrafo ancla EXACTO (se inserta justo después), HTML a insertar]. */
    private function rows(): array
    {
        $p = self::P_CLASS;
        $links = '<p class="' . $p . '">También te puede servir: <a href="https://homedelvalle.mx/blog/cuanto-cuesta-sucesion-cdmx-2026">cuánto cuesta una sucesión en CDMX</a> (con calculadora), '
            . '<a href="https://homedelvalle.mx/blog/propiedad-sin-testamento-cdmx-como-regularizar-vender-2026">cómo regularizar una propiedad sin testamento</a> y '
            . '<a href="https://homedelvalle.mx/blog/hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx">qué hacer si un heredero no quiere vender</a>.</p>';

        return [
            'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026' => [
                'anchor' => '<p class="' . $p . '">Si quieres saber cuánto podría valer tu propiedad una vez regularizada, o si tienes dudas sobre qué hacer primero, nuestro equipo puede orientarte sin costo.</p>',
                'insert' => '<p class="' . $p . '">También te puede servir: <a href="https://homedelvalle.mx/blog/cuanto-cuesta-sucesion-cdmx-2026">cuánto cuesta una sucesión en CDMX</a> (con calculadora), '
                    . '<a href="https://homedelvalle.mx/blog/isr-venta-propiedad-heredada-mexico-2026">cuánto ISR pagarías al vender</a> y '
                    . '<a href="https://homedelvalle.mx/blog/hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx">qué hacer si un heredero no quiere vender</a>.</p>',
            ],
            'isr-venta-propiedad-heredada-mexico-2026' => [
                'anchor' => '<p class="' . $p . '">Si tu propiedad heredada está en Benito Juárez, podemos orientarte sobre el proceso de venta y conectarte con el tipo de comprador que más conviene según el perfil de tu inmueble.</p>',
                'insert' => $links,
            ],
            'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx' => [
                'anchor' => '<p class="' . $p . '">Si tienes una propiedad heredada en Benito Juárez con un copropietario en desacuerdo, podemos ayudarte a:</p>',
                'insert' => '<p class="' . $p . '">Antes de eso, te puede servir: <a href="https://homedelvalle.mx/blog/cuanto-cuesta-sucesion-cdmx-2026">cuánto cuesta una sucesión en CDMX</a> (con calculadora), '
                    . '<a href="https://homedelvalle.mx/blog/propiedad-sin-testamento-cdmx-como-regularizar-vender-2026">cómo regularizar una propiedad sin testamento</a> y '
                    . '<a href="https://homedelvalle.mx/blog/isr-venta-propiedad-heredada-mexico-2026">cuánto ISR pagarías al vender</a>.</p>',
                'before' => true,   // aquí conviene ANTES del párrafo ancla, no después (ver comentario en up())
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->rows() as $slug => $row) {
            $post = DB::table('posts')->where('slug', $slug)->first();
            if (! $post || ! str_contains($post->body, $row['anchor'])) {
                continue;   // no existe aquí, o el párrafo ancla ya cambió — no se adivina dónde insertar
            }
            if (str_contains($post->body, 'También te puede servir')) {
                continue;   // ya se insertó (re-correr la migración no debe duplicar)
            }

            $replacement = ($row['before'] ?? false)
                ? $row['insert'] . $row['anchor']
                : $row['anchor'] . $row['insert'];

            DB::table('posts')->where('id', $post->id)->update([
                'body' => str_replace($row['anchor'], $replacement, $post->body),
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->rows() as $slug => $row) {
            $post = DB::table('posts')->where('slug', $slug)->first();
            if (! $post) {
                continue;
            }
            $replacement = ($row['before'] ?? false)
                ? $row['insert'] . $row['anchor']
                : $row['anchor'] . $row['insert'];

            DB::table('posts')->where('id', $post->id)->update([
                'body' => str_replace($replacement, $row['anchor'], $post->body),
            ]);
        }
    }
};
