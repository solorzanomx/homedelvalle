<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Artículo del manual: obligado solidario. */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/obligado-solidario.md');
        $catId = DB::table('help_categories')->where('slug', 'rentas')->value('id')
            ?? DB::table('help_categories')->where('slug', 'portal-cliente')->value('id');
        if (! file_exists($file) || ! $catId) {
            return;
        }

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'obligado-solidario'],
            [
                'help_category_id' => $catId,
                'title' => 'Obligado solidario: quién es, qué se le pide y cómo se sigue',
                'content' => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
                'sort_order' => 0, 'is_published' => true, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'obligado-solidario')->delete();
    }
};
