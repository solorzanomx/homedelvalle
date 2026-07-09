<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Los 11 posts sembrados ganaron marcadores editoriales de imagen
 * (hdv-img-slot: 1 principal + 3 internas por post, con la foto sugerida y
 * su posición). Re-sincroniza el body desde database/seeders/blog-posts/
 * SOLO para los que sigan en 'scheduled' — un post ya publicado o editado
 * a mano no se toca. Si la migración que los crea corre en el mismo deploy
 * (aún no había corrido en producción), esto es un no-op inofensivo.
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
        // Sin reversa — el estado anterior era el mismo contenido sin
        // marcadores; se puede regenerar borrando los divs hdv-img-slot.
    }
};
