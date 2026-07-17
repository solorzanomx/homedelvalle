<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Resync del artículo 'leads-easybroker-portales': se agregó la sección de
 * clasificación automática con IA (rol/temperatura/resumen, botón de backlog).
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/leads-easybroker-portales.md');
        if (! file_exists($file)) {
            return;
        }

        DB::table('help_articles')->where('slug', 'leads-easybroker-portales')->update([
            'content'    => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
            'updated_at' => now(),
        ]);
    }

    public function down(): void {}
};
