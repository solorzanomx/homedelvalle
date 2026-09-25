<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Resync: asistente 'Tus datos' por pasos. */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/portal-inquilino-mi-camino.md');
        $catId = DB::table('help_categories')->where('slug', 'portal-cliente')->value('id')
            ?? DB::table('help_categories')->where('slug', 'rentas')->value('id');
        if (! file_exists($file) || ! $catId) {
            return;
        }

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'portal-inquilino-mi-camino'],
            [
                'help_category_id' => $catId,
                'title' => 'Lo que ve el inquilino: Mi camino y Mis documentos',
                'content' => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
                'sort_order' => 0, 'is_published' => true, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'portal-inquilino-mi-camino')->delete();
    }
};
