<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Deja armada la taxonomía completa del blog (auditoría 2026-07-08):
 *
 * 1. Descripciones en las categorías (estaban vacías — hoy solo se ven en
 *    el admin, pero definen la línea editorial de cada sección).
 * 2. Set canónico de tags (pocos y consistentes, decisión explícita: NO un
 *    tag nuevo por post) — reutiliza por NOMBRE los que ya existen en
 *    producción (#ParaPropietarios, #Precios, #VenderPropiedad) para no
 *    duplicar, y crea los que faltan.
 * 3. Asigna tags a los 16 posts publicados existentes, por slug, sin
 *    duplicar pares ya existentes en el pivote.
 *
 * Defensiva: no-op donde el post/tabla no exista (local sin posts).
 */
return new class extends Migration
{
    private const CATEGORY_DESCRIPTIONS = [
        'zonificacion-desarrollo'   => 'Tu casa como terreno: uso de suelo, potencial constructivo y qué pagan las desarrolladoras por predios en Benito Juárez.',
        'herencias-y-sucesiones'    => 'Cómo regularizar, escriturar y vender una propiedad heredada en CDMX: sucesiones, testamentos, ISR y acuerdos entre herederos.',
        'vender-tu-propiedad'       => 'Guías prácticas para vender tu propiedad en Benito Juárez: valuación, negociación, documentos y cierre seguro.',
        'inversion-inmobiliaria'    => 'Análisis de inversión inmobiliaria en Benito Juárez: rendimientos, colonias con plusvalía y oportunidades reales.',
        'mercado-inmobiliario-cdmx' => 'Precios, tendencias y datos del mercado inmobiliario en Benito Juárez y CDMX, actualizados con fuentes reales.',
        'colonias-de-benito-juarez' => 'Guías por colonia: vivir, comprar e invertir en Del Valle, Narvarte, Nápoles, Portales, Xoco y más.',
        'renta-e-inquilinos'        => 'Rentar tu propiedad con seguridad: contratos, pólizas jurídicas, inquilinos y administración.',
    ];

    /** name => slug — set canónico. */
    private const TAGS = [
        '#VenderAConstructora' => 'vender-a-constructora',
        '#Herencias'           => 'herencias',
        '#Precios'             => 'precios',
        '#ParaPropietarios'    => 'para-propietarios',
        '#VenderPropiedad'     => 'vender-propiedad',
        '#Inversión'           => 'inversion',
        '#Narvarte'            => 'narvarte',
        '#Nápoles'             => 'napoles',
        '#DelValle'            => 'del-valle',
    ];

    /** post slug => tag names. */
    private const ASSIGNMENTS = [
        // Zonificación y Desarrollo
        'como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar' => ['#VenderAConstructora', '#ParaPropietarios'],
        'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo' => ['#VenderAConstructora', '#ParaPropietarios'],
        'vender-casa-terreno-constructores-benito-juarez-2026' => ['#VenderAConstructora', '#ParaPropietarios'],
        // Herencias y Sucesiones
        'como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026' => ['#Herencias', '#VenderPropiedad'],
        'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx' => ['#Herencias'],
        'isr-venta-propiedad-heredada-mexico-2026' => ['#Herencias', '#VenderPropiedad'],
        'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026' => ['#Herencias'],
        'vender-propiedad-heredada-benito-juarez-2026' => ['#Herencias', '#VenderPropiedad'],
        // Mercado
        'precio-metro-cuadrado-colonias-benito-juarez-2026' => ['#Precios', '#ParaPropietarios'],
        // Vender tu Propiedad
        'como-valuar-una-propiedad-en-mexico' => ['#Precios', '#VenderPropiedad'],
        'negociacion-venta-propiedad-benito-juarez' => ['#VenderPropiedad', '#ParaPropietarios'],
        'vender-o-rentar-propiedad-benito-juarez-analisis-2026' => ['#ParaPropietarios', '#VenderPropiedad'],
        'vender-propiedad-con-inquilinos-cdmx-derecho-del-tanto-2026' => ['#VenderPropiedad', '#ParaPropietarios'],
        // Inversión
        'invertir-en-napoles-o-acacias-benito-juarez-2026' => ['#Inversión', '#Nápoles'],
        'invertir-en-narvarte-2026-guia-completa' => ['#Inversión', '#Narvarte'],
        'invertir-inmuebles-benito-juarez-2026' => ['#Inversión'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('post_categories') || ! Schema::hasTable('tags') || ! Schema::hasTable('post_tag')) {
            return;
        }

        // 1. Descripciones de categorías (solo si están vacías — no pisar
        //    una descripción que se haya editado a mano en el admin).
        foreach (self::CATEGORY_DESCRIPTIONS as $slug => $description) {
            DB::table('post_categories')
                ->where('slug', $slug)
                ->where(fn ($q) => $q->whereNull('description')->orWhere('description', ''))
                ->update(['description' => $description]);
        }

        // 2. Tags canónicos — match por NOMBRE (los existentes en producción
        //    se crearon con estos nombres exactos pero slug desconocido).
        $tagIds = [];
        foreach (self::TAGS as $name => $slug) {
            $existing = DB::table('tags')->where('name', $name)->first();
            if ($existing) {
                if (empty($existing->slug)) {
                    DB::table('tags')->where('id', $existing->id)->update(['slug' => $slug]);
                }
                $tagIds[$name] = $existing->id;
            } else {
                $tagIds[$name] = DB::table('tags')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Asignación a posts existentes, sin duplicar pares del pivote.
        foreach (self::ASSIGNMENTS as $postSlug => $tagNames) {
            $postId = DB::table('posts')->where('slug', $postSlug)->value('id');
            if (! $postId) {
                continue;
            }

            foreach ($tagNames as $tagName) {
                $tagId = $tagIds[$tagName] ?? null;
                if (! $tagId) {
                    continue;
                }

                $exists = DB::table('post_tag')
                    ->where('post_id', $postId)
                    ->where('tag_id', $tagId)
                    ->exists();

                if (! $exists) {
                    DB::table('post_tag')->insert(['post_id' => $postId, 'tag_id' => $tagId]);
                }
            }
        }
    }

    public function down(): void
    {
        // Seed de taxonomía editorial — quitarla a mano desde el admin si
        // hiciera falta; revertir en masa borraría también asignaciones
        // hechas manualmente después.
    }
};
