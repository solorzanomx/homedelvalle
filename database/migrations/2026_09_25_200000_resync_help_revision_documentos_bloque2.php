<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Resync del artículo 'revision-documentos-portal' (bloque 2: bloque, comparación, recordatorios, métricas, asistente de subida). */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/revision-documentos-portal.md');
        if (! file_exists($file)) {
            return;
        }

        $content = preg_replace('/^# .+\n+/', '', file_get_contents($file), 1);

        DB::table('help_articles')->where('slug', 'revision-documentos-portal')->update([
            'content' => $content,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Sin reversa: el contenido anterior sigue en el historial de git.
    }
};
