<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Artículo del manual: Recibo de Pago Parcial (venta). */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/recibo-pago-parcial.md');
        $catId = DB::table('help_categories')->where('slug', 'venta')->value('id');
        if (! file_exists($file) || ! $catId) {
            return;
        }

        DB::table('help_articles')->updateOrInsert(
            ['slug' => 'recibo-pago-parcial'],
            [
                'help_category_id' => $catId,
                'title' => 'Recibo de Pago Parcial: un recibo por cada pago',
                'content' => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
                'sort_order' => 0, 'is_published' => true, 'view_count' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('help_articles')->where('slug', 'recibo-pago-parcial')->delete();
    }
};
