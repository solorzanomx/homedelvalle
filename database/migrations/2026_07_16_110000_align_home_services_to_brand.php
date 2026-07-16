<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Alineación del home al posicionamiento constructor-primero
 * (docs/posicionamiento-marca.md, docs/recomendaciones-sitio-web.md).
 *
 * El blade ya trae la tarjeta protagonista de Desarrollo Inmobiliario y los
 * defaults corregidos, pero services_section guardado en site_settings pisa
 * los defaults — aquí se corrige la copia de BD: ninguna tarjeta del grid
 * debe quedar 'highlighted' (el badge "★ Más solicitado" de venta tradicional
 * contradecía el negocio principal). También actualiza el subheading si aún
 * es el texto default anterior.
 *
 * Defensiva: no-op si site_settings no existe o services_section es null.
 */
return new class extends Migration
{
    public function up(): void
    {
        $settings = \App\Models\SiteSetting::first();
        if (! $settings) {
            return;
        }

        $services = $settings->services_section;
        if (is_array($services)) {
            foreach ($services as &$service) {
                $service['highlighted'] = false;
                unset($service['badge_text']);
            }
            $settings->services_section = $services;
        }

        if ($settings->services_subheading === 'Cuatro funnels especializados para cada perfil del mercado inmobiliario.') {
            $settings->services_subheading = 'Nuestra especialidad — conectar predios con desarrolladoras — respaldada por cuatro líneas de soporte.';
        }

        $settings->save();
    }

    public function down(): void
    {
        // Sin reversa: el estado anterior (venta tradicional destacada con
        // "★ Más solicitado") era la contradicción que esta migración corrige.
    }
};
