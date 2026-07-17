<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Continuación de 2026_07_17_030000: los correos de acuse (los que recibe el
 * lead al enviar un formulario) viven en acuse_email_configs y en producción
 * fueron sembrados con "valuación" — se corrigen a "opinión de valor".
 * El seeder ya quedó corregido para siembras futuras. No-op si la tabla está
 * vacía (local).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('acuse_email_configs')) {
            return;
        }

        $reemplazos = [
            'Recibimos tu solicitud de valuación' => 'Recibimos tu solicitud de opinión de valor',
            'Vamos a preparar la valuación de tu' => 'Vamos a preparar la opinión de valor de tu',
            'Te enviamos la valuación'            => 'Te enviamos la opinión de valor',
            'Valuación gratuita'                  => 'Opinión de valor gratuita',
            'valuación gratuita'                  => 'opinión de valor gratuita',
            'tu valuación'                        => 'tu opinión de valor',
        ];

        foreach (DB::table('acuse_email_configs')->get() as $row) {
            $updates = [];
            foreach ((array) $row as $col => $val) {
                if (is_string($val)) {
                    $nuevo = strtr($val, $reemplazos);
                    if ($nuevo !== $val) {
                        $updates[$col] = $nuevo;
                    }
                }
            }
            if ($updates) {
                DB::table('acuse_email_configs')->where('id', $row->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Sin reversa.
    }
};
