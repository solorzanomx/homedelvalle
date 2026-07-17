<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Resync de 'seguimiento-de-leads': Día 0 con el paso de Notas internas
 * como contexto de la IA y la firma personal del asesor.
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
