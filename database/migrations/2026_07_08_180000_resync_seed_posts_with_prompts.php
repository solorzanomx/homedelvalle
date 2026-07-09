<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Los prompts en inglés (Nano Banana/Flow) se agregaron a los archivos de
 * los 11 posts DESPUÉS de que un deploy intermedio ya había corrido la
 * resincronización anterior (160000) con los archivos sin prompts — las
 * migraciones corren una sola vez, así que ese deploy dejó los posts con
 * los marcadores viejos. Esta migración (timestamp nuevo) vuelve a
 * sincronizar el body desde los archivos finales, SOLO para posts que
 * sigan en 'scheduled' (un post publicado o editado a mano no se toca).
 *
 * Lección: cualquier cambio a database/seeders/blog-posts/*.html que deba
 * llegar a posts ya sembrados necesita SIEMPRE su propia migración de
 * resync nueva — editar los archivos no basta si la siembra ya corrió.
 */
return new class extends Migration
{
    private const SLUGS = [
        'cuanto-pagan-constructoras-terreno-del-valle-2026',
        'vender-casa-constructora-proceso-tiempos-cdmx',
        'como-consultar-uso-de-suelo-benito-juarez-seduvi',
        'vender-terreno-junto-con-vecinos-desarrolladora',
        'fideicomiso-o-venta-directa-desarrolladora',
        'cuanto-cuesta-sucesion-cdmx-2026',
        'vender-propiedad-heredada-entre-hermanos-sin-conflicto',
        'vivir-en-narvarte-precios-pros-contras-2026',
        'vivir-en-del-valle-precios-pros-contras-2026',
        'vivir-en-portales-precios-pros-contras-2026',
        'van-a-bajar-precios-departamentos-benito-juarez-2026',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        foreach (self::SLUGS as $slug) {
            $file = database_path('seeders/blog-posts/' . $slug . '.html');
            if (! file_exists($file)) {
                continue;
            }

            DB::table('posts')
                ->where('slug', $slug)
                ->where('status', 'scheduled')
                ->update(['body' => file_get_contents($file), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Resync de contenido — sin reversa.
    }
};
