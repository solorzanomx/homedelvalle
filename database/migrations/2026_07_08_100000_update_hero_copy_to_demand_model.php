<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El hero del home es editable vía SiteSetting y la BD pisa los defaults del
 * blade — cambiar solo public/home.blade.php no se reflejaría en producción
 * si estos campos ya fueron guardados alguna vez desde el admin. Esta
 * migración fija el mensaje nuevo (modelo predios→desarrolladoras, pedido
 * explícito 2026-07-08) de forma determinista; sigue siendo editable desde
 * el admin después.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')->limit(1)->update([
            'hero_badge'      => 'Firma boutique en Benito Juárez · 30+ años',
            'hero_heading'    => 'Operamos desde la demanda, no desde la oferta.',
            'hero_subheading' => 'Constructoras de nuestra cartera buscan predios en Benito Juárez ahora mismo — tu casa podría valer más como terreno. También vendemos, rentamos y encontramos inmuebles con asesoría en menos de 24 horas.',
        ]);
    }

    public function down(): void
    {
        // El copy anterior era el default del blade (campos en NULL) — volver
        // a NULL restaura exactamente el comportamiento previo.
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')->limit(1)->update([
            'hero_badge'      => null,
            'hero_heading'    => null,
            'hero_subheading' => null,
        ]);
    }
};
