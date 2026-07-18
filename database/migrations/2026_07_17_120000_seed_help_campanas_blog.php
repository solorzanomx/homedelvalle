<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Artículo del manual: campañas de blog con IA (regla: cada módulo nuevo entrega su artículo). */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/campanas-blog-ia.md');
        if (! file_exists($file)) {
            return;
        }

        $catId = DB::table('help_categories')->where('slug', 'cms')->value('id');
        if (! $catId) {
            return;
        }

        $content = preg_replace('/^# .+\n+/', '', file_get_contents($file), 1);

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'campanas-blog-ia'],
            [
                'help_category_id' => $catId,
                'title'            => 'Campañas de blog con IA: del brief a publicar en automático',
                'content'          => $content,
                'sort_order'       => 0,
                'is_published'     => true,
                'view_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'campanas-blog-ia')->delete();
    }
};
