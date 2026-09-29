<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 5.3 del prompt de leads del blog: FAQPage en los artículos de herencias, con preguntas que ya
 * estaban respondidas en el texto (ninguna se inventó). `isr-venta-propiedad-heredada-mexico-2026` ya
 * tenía faq_schema (verificado leyendo la BD real) — se deja igual. El render (blog/show.blade.php)
 * ya soporta faq_schema desde antes; esta migración solo llena el dato que faltaba en los otros 3.
 */
return new class extends Migration
{
    private function rows(): array
    {
        return [
            'cuanto-cuesta-sucesion-cdmx-2026' => [
                ['q' => '¿Cuánto tiempo tarda una sucesión con testamento en CDMX?', 'a' => 'De 2 a 6 meses, dependiendo de qué tan completa esté la documentación: acta de defunción, testamento, escritura del inmueble, predial y agua al corriente.'],
                ['q' => '¿Cuánto tarda una sucesión sin testamento?', 'a' => 'De 1 a 3 años, según la carga del juzgado y qué tanto se compliquen las notificaciones y acuerdos entre herederos.'],
                ['q' => '¿De qué depende el costo de una sucesión?', 'a' => 'Principalmente del valor del inmueble: los honorarios notariales y el ISAI se calculan sobre ese valor, más certificados, avalúo y derechos de inscripción en el Registro Público.'],
                ['q' => '¿Conviene vender el inmueble heredado durante la sucesión?', 'a' => 'Sí puede coordinarse: la adjudicación se puede alinear con la venta para que los tiempos no se dupliquen, y hay compradores dispuestos a esperar un proceso sucesorio bien encaminado.'],
            ],
            'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026' => [
                ['q' => '¿Qué significa que una propiedad esté "intestada"?', 'a' => 'Que el titular falleció sin dejar testamento válido. El inmueble sigue siendo de la familia, pero legalmente no está en manos de nadie hasta que un juez o notario lo determine de forma oficial.'],
                ['q' => '¿Se puede vender una propiedad intestada?', 'a' => 'No. Mientras no se tramite la sucesión, la propiedad no puede venderse válidamente — ningún notario escriturará una venta si el vendedor es una persona fallecida.'],
                ['q' => '¿Cuánto tarda regularizar una propiedad sin testamento?', 'a' => 'Ante notario, si hay acuerdo entre herederos y documentación completa, aproximadamente seis meses. Ante juzgado, cuando hay complicaciones, entre uno y dos años.'],
                ['q' => '¿Existe un beneficio fiscal para regularizar una sucesión en CDMX?', 'a' => 'Sí: la Jornada Notarial de la Ciudad de México da descuentos del 40% al 80% sobre impuestos, derechos y honorarios notariales en sucesiones, calculados sobre el valor catastral (no el comercial) del inmueble.'],
            ],
            'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx' => [
                ['q' => '¿Puede un heredero vender el inmueble completo sin el consentimiento de los demás?', 'a' => 'No. Ningún heredero puede vender el inmueble completo sin el consentimiento de los demás — un notario no escriturará la venta de la totalidad si falta la firma de algún copropietario.'],
                ['q' => '¿Qué opciones hay si un heredero no quiere vender?', 'a' => 'De menor a mayor fricción: negociación directa con mediación, que los demás herederos compren su parte alícuota, ofrecerla a un tercero (los demás copropietarios tienen derecho de preferencia), o la partición judicial del inmueble.'],
                ['q' => '¿Cuánto tarda un juicio de partición?', 'a' => 'Entre uno y tres años, dependiendo de la complejidad del caso y si hay impugnaciones.'],
                ['q' => '¿Cuándo conviene ir al juzgado por un conflicto entre herederos?', 'a' => 'Cuando un solo heredero bloquea sin razón legítima, ya se agotaron las vías de negociación, el valor del inmueble justifica el costo y tiempo del proceso, y los demás herederos tienen liquidez para sostenerlo.'],
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->rows() as $slug => $faq) {
            DB::table('posts')->where('slug', $slug)->whereNull('faq_schema')->update(['faq_schema' => json_encode($faq, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public function down(): void
    {
        DB::table('posts')->whereIn('slug', array_keys($this->rows()))->update(['faq_schema' => null]);
    }
};
