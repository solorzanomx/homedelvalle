<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_agent_configs')->insertOrIgnore([
            [
                'key'         => 'carousel.image_generation',
                'label'       => 'Generación de Imágenes',
                'description' => 'Genera imágenes de fondo para cada slide del carrusel vía DALL-E u otro proveedor.',
                'provider'    => 'openai',
                'model'       => 'dall-e-3',
                'max_tokens'  => 0,
                'temperature' => 0.00,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('ai_agent_configs')->where('key', 'carousel.image_generation')->delete();
    }
};
