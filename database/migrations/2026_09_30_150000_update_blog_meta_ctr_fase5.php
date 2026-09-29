<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 5.2 del prompt de leads del blog: titles/metas de los artículos con muchas impresiones y
 * CTR bajo (datos de GSC que trajo Alejandro). Solo SERP: no toca H1, slug ni contenido. No-op si
 * el post no existe (local no tiene el catálogo real — ver docs/funcionalidades/*blog*).
 *
 * `precio-metro-cuadrado-colonias-benito-juarez-2026` NO está en esta lista a propósito: su meta ya
 * se corrigió en la ronda de julio (migración 2026_07_16_100000_update_blog_meta_for_ctr) y ya
 * coincide casi textual con lo que pedía este prompt — nada que tocar.
 */
return new class extends Migration
{
    /** slug => [meta_title anterior, meta_description anterior] — respaldo para down(). */
    private const OLD = [
        'usufructo-vitalicio-que-pasa-al-morir-benito-juarez' => [
            'Usufructo vitalicio al morir | Benito Juárez | Home del Vall...',
            '¿Qué pasa con el usufructo vitalicio cuando fallece el usufructuario en Benito Juárez, CDMX? Proceso legal, trámites y cómo maximizar tu patrimonio.',
        ],
        'uso-de-suelo-h4-benito-juarez' => [
            'Uso de suelo H4 Benito Juárez | Home del Valle',
            'Descubre qué permite construir la zonificación H4 en Benito Juárez: 4 niveles, densidad media y usos mixtos. Conoce el potencial de tu predio hoy.',
        ],
        'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo' => [
            'Uso de Suelo H5 y H6 en Benito Juárez: ¿Tu Casa Vale Más como Terreno?',
            'Qué significan H5 y H6, cómo saber cuántos niveles permite tu predio y por qué las desarrolladoras pagan más por casas con esta zonificación en Del Valle, Narvarte y Nápoles.',
        ],
        'vender-propiedad-heredada-sin-escrituras-benito-juarez' => [
            'Vender propiedad heredada sin escrituras | Benito Juárez',
            '¿Heredaste una casa en Benito Juárez y quieres venderla? Aprende qué pasos legales necesitas antes de escriturar y cómo maximizar su valor. Guía 2026.',
        ],
        'vivir-en-del-valle-sur-cdmx-diferencias-centro-norte' => [
            'Vivir en Del Valle Sur CDMX 2026 | Home del Valle',
            'Descubre qué hace única a Del Valle Sur frente a Centro y Norte: precios, servicios y plusvalía en Benito Juárez. Consulta el Observatorio de precios.',
        ],
        'vivir-en-napoles-cdmx-precios-pros-contras-2026' => [
            'Vivir en Nápoles CDMX 2026 | Home del Valle',
            "Descubre qué significa vivir en Nápoles CDMX en 2026: plusvalía, precios, pros y contras. Guía boutique de Home del Valle para propietarios e inversionista...",
        ],
        'vivir-en-narvarte-precios-pros-contras-2026' => [
            'Vivir en Narvarte: precios, pros y contras 2026',
            'Cómo es vivir en la colonia Narvarte en 2026: precios por m² reales, conectividad, perfil de la colonia, sus contras honestos y si conviene como inversión.',
        ],
        'cuanto-cuesta-sucesion-cdmx-2026' => [
            '¿Cuánto cuesta una sucesión en CDMX? Notaría vs juicio',
            'Costos y tiempos reales de una sucesión en CDMX en 2026: testamentaria ante notario vs juicio intestamentario, de qué depende el precio y las 3 decisiones que lo abaratan.',
        ],
    ];

    /** slug => [meta_title nuevo, meta_description nueva]. */
    private const NEW = [
        'usufructo-vitalicio-que-pasa-al-morir-benito-juarez' => [
            'Usufructo vitalicio: qué pasa con la casa al morir (CDMX)',
            null,   // la descripción actual ya está bien (153 car., beneficio concreto) — solo el title estaba roto (cortado a media palabra)
        ],
        'uso-de-suelo-h4-benito-juarez' => [
            'Uso de suelo H4 en Benito Juárez: cuántos pisos y cuánto vale',
            null,   // ya estaba en rango y con cifra concreta (4 niveles) — sin cambio
        ],
        'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo' => [
            '¿Tu casa es H5 o H6? Así la valúa una desarrolladora (BJ)',
            'Qué significan H5 y H6 en Benito Juárez y por qué las desarrolladoras pagan más por casas con esta zonificación en Del Valle, Narvarte y Nápoles.',
        ],
        'vender-propiedad-heredada-sin-escrituras-benito-juarez' => [
            '¿Vender una casa heredada sin escrituras? Sí se puede: pasos',
            null,   // ya estaba en rango — sin cambio
        ],
        'vivir-en-del-valle-sur-cdmx-diferencias-centro-norte' => [
            'Del Valle Sur vs Centro vs Norte: precios y diferencias 2026',
            null,   // ya estaba en rango — sin cambio
        ],
        'vivir-en-napoles-cdmx-precios-pros-contras-2026' => [
            'Vivir en la Nápoles 2026: precios, pros y contras',
            'Vivir en Nápoles CDMX en 2026: plusvalía, precios por m², pros y contras reales. Guía boutique de Home del Valle para propietarios e inversionistas.',
        ],
        'vivir-en-narvarte-precios-pros-contras-2026' => [
            'Vivir en Narvarte 2026: precios, Oriente vs Poniente',   // verificado: el post SÍ compara Oriente vs Poniente en el cuerpo
            null,   // ya estaba en rango — sin cambio
        ],
        'cuanto-cuesta-sucesion-cdmx-2026' => [
            '¿Cuánto cuesta una sucesión en CDMX 2026? Tabla de costos',   // ahora sí hay tabla (Fase 5.1)
            'Tabla de costos de una sucesión en CDMX 2026: notaría vs juicio intestamentario, de qué depende el precio y las 3 decisiones que lo abaratan.',
        ],
    ];

    public function up(): void
    {
        foreach (self::NEW as $slug => [$title, $desc]) {
            $update = ['meta_title' => $title];
            if ($desc !== null) {
                $update['meta_description'] = $desc;
            }
            DB::table('posts')->where('slug', $slug)->update($update);
        }
    }

    public function down(): void
    {
        foreach (self::OLD as $slug => [$title, $desc]) {
            DB::table('posts')->where('slug', $slug)->update(['meta_title' => $title, 'meta_description' => $desc]);
        }
    }
};
