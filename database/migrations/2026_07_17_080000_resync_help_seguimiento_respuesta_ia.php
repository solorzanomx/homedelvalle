<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Resync de 'seguimiento-de-leads': el Día 0 ahora usa la respuesta
 * sugerida por IA (tarjeta 💬 en la ficha, WhatsApp precargado).
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/seguimiento-de-leads.md');
        if (! file_exists($file)) {
            return;
        }

        DB::table('help_articles')->where('slug', 'seguimiento-de-leads')->update([
            'content'    => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
            'updated_at' => now(),
        ]);
    }

    public function down(): void {}
};
