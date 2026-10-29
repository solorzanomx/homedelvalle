<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Artículo del manual: Clientes persona moral (empresas). */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/clientes-persona-moral.md');
        $catId = DB::table('help_categories')->where('slug', 'rentas')->value('id');
        if (! file_exists($file) || ! $catId) {
            return;
        }

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'clientes-persona-moral'],
            [
                'help_category_id' => $catId,
                'title' => 'Cuando el inquilino (o propietario) es una empresa',
                'content' => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
                'sort_order' => 0, 'is_published' => true, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'clientes-persona-moral')->delete();
    }
};
