<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Corrección de marca en /vende-tu-propiedad (pedida por Alejandro 2026-07-16):
 * la FAQ decía "No. Trabajamos sin exclusivas forzadas" — lo CONTRARIO del
 * modelo real (no se trabaja sin Acuerdo de Representación), el tiempo de
 * venta es 45-60 días y la comisión es 5%.
 *
 * Los defaults del código ya se corrigieron en LandingController; esta
 * migración corrige la copia editada guardada en site_settings.vender_content
 * (cuando existe, pisa los defaults). Defensiva: solo toca las entradas que
 * aún tienen el texto viejo.
 */
return new class extends Migration
{
    public function up(): void
    {
        $settings = \App\Models\SiteSetting::first();
        if (! $settings || ! is_array($settings->vender_content)) {
            return;
        }

        $content = $settings->vender_content;
        $dirty   = false;

        if (! empty($content['subheading']) && str_contains($content['subheading'], 'Sin exclusivas forzadas')) {
            $content['subheading'] = 'Trabajamos tu propiedad en exclusiva, con un plan de venta dedicado y compradores calificados. Sin comisiones ocultas.';
            $dirty = true;
        }

        if (! empty($content['faqs']) && is_array($content['faqs'])) {
            $reemplazos = [
                '¿Cuánto cuesta la asesoría?' =>
                    'La asesoría y la opinión de valor inicial son completamente gratuitas y sin compromiso. Nuestra comisión es del 5% y se cobra únicamente al cerrar exitosamente la venta — nunca por adelantado.',
                '¿Cuánto tiempo toma vender mi propiedad?' =>
                    'En promedio, nuestras propiedades se venden en 45 a 60 días. Depende del precio, la ubicación y las condiciones del mercado, pero nuestra estrategia de comercialización dirigida acelera el proceso.',
                '¿Necesito firmar un contrato de exclusividad?' =>
                    'Sí — trabajamos mediante un Acuerdo de Representación, y es tu mejor garantía: nos permite invertir de verdad en tu propiedad (fotografía profesional, marketing y compradores calificados) con un plan de venta dedicado y reportes de avance. Nuestro modelo boutique exige atención total a cada propiedad que representamos, y eso solo es posible con un compromiso mutuo.',
            ];

            foreach ($content['faqs'] as &$faq) {
                $q = trim($faq['q'] ?? '');
                if (isset($reemplazos[$q]) && ($faq['a'] ?? '') !== $reemplazos[$q]) {
                    $faq['a'] = $reemplazos[$q];
                    $dirty = true;
                }
            }
            unset($faq);
        }

        if ($dirty) {
            $settings->vender_content = $content;
            $settings->save();
        }
    }

    public function down(): void
    {
        // Sin reversa: el texto anterior contradecía el modelo de negocio.
    }
};
