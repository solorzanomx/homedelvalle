<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Resync del artículo de ayuda 'leads-easybroker-portales': se agregó el
 * abordaje desde la ficha (WhatsApp 1-clic, tarjeta de propiedad, alerta 2 h)
 * y la detección/registro de brokers de colaboración. Los .md de
 * database/seeders/help-articles solo se leen al sembrar — cada cambio
 * necesita su propia migración de resync (regla del Manual de Operación).
 */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/leads-easybroker-portales.md');
        if (! file_exists($file)) {
            return;
        }

        $content = preg_replace('/^# .+\n+/', '', file_get_contents($file), 1);

        DB::table('help_articles')->where('slug', 'leads-easybroker-portales')->update([
            'content'    => $content,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // El contenido anterior vive en el historial de git del .md.
    }
};
