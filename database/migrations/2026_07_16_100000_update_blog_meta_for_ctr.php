<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reescribe meta_title/meta_description de los 5 posts con más impresiones
 * y peor CTR en Search Console (datos al 2026-07-16: p.ej. precio-m2 con
 * 1,861 impresiones y 0.7% de clics). Solo cambia lo que ve Google en el
 * SERP — no toca title (H1), slug ni contenido.
 *
 * Defensiva: si un slug no existe (BD local), no hace nada.
 * Los valores anteriores quedan registrados en el mensaje del commit.
 */
return new class extends Migration
{
    public function up(): void
    {
        $metas = [
            'precio-metro-cuadrado-colonias-benito-juarez-2026' => [
                'meta_title'       => 'Precio por m² en Benito Juárez 2026: Tabla por Colonia',
                'meta_description' => '¿Cuánto vale el m² en Del Valle, Nápoles, Narvarte, Portales o Xoco en 2026? Tabla actualizada con precios reales por colonia — y cuánto más pagaría una desarrolladora por tu predio.',
            ],
            'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026' => [
                'meta_title'       => 'Propiedad sin Testamento en CDMX: Regularizar y Vender (2026)',
                'meta_description' => '¿Tu familiar murió sin testamento? Qué tramitar primero, cuánto cuesta, cuánto tarda y el beneficio fiscal que casi nadie aprovecha al vender. Guía CDMX 2026 paso a paso.',
            ],
            'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo' => [
                'meta_title'       => 'Uso de Suelo H5 y H6 en Benito Juárez: ¿Tu Casa Vale Más como Terreno?',
                'meta_description' => 'Qué significan H5 y H6, cómo saber cuántos niveles permite tu predio y por qué las desarrolladoras pagan más por casas con esta zonificación en Del Valle, Narvarte y Nápoles.',
            ],
            'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx' => [
                'meta_title'       => 'Mi Hermano No Quiere Vender la Propiedad Heredada: Qué Hacer (CDMX)',
                'meta_description' => '¿Un coheredero bloquea la venta? Opciones legales reales en CDMX 2026: negociación, compra de su parte y partición judicial — con costos y tiempos de cada camino.',
            ],
            'isr-venta-propiedad-heredada-mexico-2026' => [
                'meta_title'       => 'ISR al Vender Propiedad Heredada 2026: Cuándo y Cuánto Pagas',
                'meta_description' => 'No siempre aplica la exención al vender una herencia: cuándo sí pagas ISR, cuánto exactamente y cómo calcularlo, con ejemplos reales. Guía México 2026.',
            ],
        ];

        foreach ($metas as $slug => $meta) {
            DB::table('posts')->where('slug', $slug)->update($meta);
        }
    }

    public function down(): void
    {
        // Sin reversa automática: los valores previos de producción están
        // documentados en el commit que introdujo esta migración.
    }
};
