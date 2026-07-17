<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Artículo 'Seguimiento de leads: el procedimiento oficial' — la política
 * acordada con Alejandro 2026-07-17 (regla de las 3 R, la visita como
 * bautizo de conversión, cadencia de 5 toques, semántica de estados).
 * Va primero en la categoría Leads y clientes: es EL procedimiento.
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/seguimiento-de-leads.md');
        if (! file_exists($file)) {
            return;
        }

        $catId = DB::table('help_categories')->where('slug', 'clientes-leads')->value('id');
        if (! $catId) {
            return;
        }

        $content = preg_replace('/^# .+\n+/', '', file_get_contents($file), 1);

        $existing = DB::table('help_articles')->where('slug', 'seguimiento-de-leads')->first();
        $data = [
            'help_category_id' => $catId,
            'title'            => 'Seguimiento de leads: el procedimiento oficial',
            'content'          => $content,
            'sort_order'       => 0, // primero de la categoría
            'is_published'     => true,
            'updated_at'       => now(),
        ];

        if ($existing) {
            DB::table('help_articles')->where('id', $existing->id)->update($data);
        } else {
            DB::table('help_articles')->insert($data + ['slug' => 'seguimiento-de-leads', 'view_count' => 0, 'created_at' => now()]);
        }
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'seguimiento-de-leads')->delete();
    }
};
