<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría del blog 2026-07-08: 16 posts publicados y solo 2 categorías en
 * uso — 12 amontonados en "vender-tu-propiedad" (incluidos los 3 de
 * terreno/desarrollo y los 5 de herencias) y 4 en "inversion-inmobiliaria".
 * Importa porque el CTA final de cada post se elige POR categoría
 * (blog/show.blade.php): los posts de terreno mostraban el CTA genérico de
 * vendedor en vez del funnel predio→desarrolladora, y el cluster de
 * herencias (lectores con urgencia real de vender) no tenía CTA propio.
 *
 * - Crea la categoría "Herencias y Sucesiones" (5 posts existentes la
 *   justifican solos) y mueve ese cluster.
 * - Mueve los 3 posts de terreno/potencial a "zonificacion-desarrollo"
 *   (existía vacía) — su CTA ahora apunta a /vende-a-desarrolladora.
 * - Mueve el post de precios por m² a "mercado-inmobiliario-cdmx" — su CTA
 *   de categoría es "¿Cuánto vale tu propiedad hoy?".
 *
 * Defensiva: no-op donde el post o la categoría no existan (local sin posts).
 */
return new class extends Migration
{
    private const MOVES = [
        'herencias-y-sucesiones' => [
            'como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026',
            'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx',
            'isr-venta-propiedad-heredada-mexico-2026',
            'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026',
            'vender-propiedad-heredada-benito-juarez-2026',
        ],
        'zonificacion-desarrollo' => [
            'como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar',
            'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo',
            'vender-casa-terreno-constructores-benito-juarez-2026',
        ],
        'mercado-inmobiliario-cdmx' => [
            'precio-metro-cuadrado-colonias-benito-juarez-2026',
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasTable('post_categories')) {
            return;
        }

        // La categoría del cluster de herencias no existía.
        if (! DB::table('post_categories')->where('slug', 'herencias-y-sucesiones')->exists()) {
            DB::table('post_categories')->insert([
                'name'        => 'Herencias y Sucesiones',
                'slug'        => 'herencias-y-sucesiones',
                'description' => 'Cómo regularizar, escriturar y vender una propiedad heredada en CDMX: sucesiones, testamentos, ISR y acuerdos entre herederos.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        foreach (self::MOVES as $categorySlug => $postSlugs) {
            $categoryId = DB::table('post_categories')->where('slug', $categorySlug)->value('id');
            if (! $categoryId) {
                continue;
            }

            DB::table('posts')
                ->whereIn('slug', $postSlugs)
                ->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('posts') || ! Schema::hasTable('post_categories')) {
            return;
        }

        // Estado previo: todos estos posts vivían en "vender-tu-propiedad"
        // (los de inversión no se tocaron en up()).
        $vendedorId = DB::table('post_categories')->where('slug', 'vender-tu-propiedad')->value('id');
        if ($vendedorId) {
            $all = array_merge(...array_values(self::MOVES));
            // vender-casa-terreno venía de inversion-inmobiliaria
            $inversionId = DB::table('post_categories')->where('slug', 'inversion-inmobiliaria')->value('id');
            DB::table('posts')->whereIn('slug', $all)->update(['category_id' => $vendedorId]);
            if ($inversionId) {
                DB::table('posts')->where('slug', 'vender-casa-terreno-constructores-benito-juarez-2026')
                    ->update(['category_id' => $inversionId]);
            }
        }
    }
};
