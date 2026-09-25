<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Artículo del manual: revisión de documentos (visor, calidad, rechazo con motivo). */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/revision-documentos-portal.md');
        if (! file_exists($file)) {
            return;
        }

        $catId = DB::table('help_categories')->where('slug', 'portal-cliente')->value('id');
        if (! $catId) {
            return;
        }

        $content = preg_replace('/^# .+\n+/', '', file_get_contents($file), 1);

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'revision-documentos-portal'],
            [
                'help_category_id' => $catId,
                'title'            => 'Revisar documentos: visor, calidad y rechazo con motivo',
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
        DB::table('help_articles')->where('slug', 'revision-documentos-portal')->delete();
    }
};
