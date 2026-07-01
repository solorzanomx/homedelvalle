<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Llena "description" (cómo abordar cada tema, tono de guía para el
 * broker) en los ítems de checklist de captación ya sembrados por
 * 2026_07_01_120000_replace_captacion_checklist_templates.php. Contenido
 * adaptado de docs/08-MANUAL-BROKER-CAPTACION.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        $descriptions = [
            // LEAD
            'Llamar al propietario (objetivo: menos de 1 hora desde que llega el lead)' =>
                'Objetivo de la llamada: confirmar interés real, entender la urgencia (¿por qué vende/renta?, ¿tiene prisa?) y dejar el camino abierto a una visita.',
            'Confirmar interés real y motivo (vender/rentar, por qué, qué tanta prisa tiene)' =>
                'Entender el motivo real te ayuda a saber qué tan urgente es para el propietario y cómo enfocar la negociación más adelante.',
            'Registrar datos básicos del inmueble (dirección, tipo, m² aproximados)' =>
                'Con estos datos el sistema ya puede calcular una referencia de precio de la zona antes de tu siguiente llamada o visita.',

            // CONTACTO
            'Enviar Presentación de Home del Valle (email o WhatsApp)' =>
                'Mándala por WhatsApp o email — el sistema genera un link rastreable para saber si la vio. No la mandes sin haber hablado antes por teléfono, pierde el toque personal.',
            'Agendar la visita' =>
                'Agéndala en esta misma llamada, no dejes "te aviso" sin fecha concreta — no dejes que se enfríe el interés.',
            'Confirmar que el propietario recibió/vio la Presentación' =>
                'Opcional, pero te da una señal de qué tan enganchado está antes de la visita.',

            // VISITA
            'Confirmar la visita con el propietario (día antes)' =>
                'Reduce las cancelaciones de último momento. Aprovecha para revisar el precio de referencia de la colonia en el Observatorio antes de ir.',
            'Realizar la visita y documentar el estado del inmueble (fotos, notas)' =>
                'Fotos y notas del estado real del inmueble — te van a servir para la valuación y para la propuesta de servicios.',
            'Dar la Opinión de Valor en el momento (Valor Rápido o Valuación Constructor)' =>
                'No te vayas prometiendo "te aviso en unos días" — el propietario que se va sin un número concreto tiene tiempo de comparar con otra inmobiliaria mientras espera.',
            'Presentar la Propuesta de Servicios ahí mismo (modo "en vivo")' =>
                'Explica el plan de comercialización apoyándote en comparables reales de la zona, no en generalidades.',
            'Ofrecer firmar la exclusiva en el momento si el propietario está listo' =>
                'Si el propietario está listo, no esperes — no hay mejor momento que el de máximo interés.',

            // REVISION_DOCS
            'Identificación oficial' =>
                'Puede subirla el propietario directo desde su Portal del Cliente, sin que tengas que ir por ella.',
            'CURP' =>
                'Se puede tramitar en línea (gob.mx) si el propietario no la tiene a la mano.',
            'Comprobante de domicilio' =>
                'No más de 3 meses de antigüedad, a nombre del propietario o con carta bajo protesta si no coincide.',
            'Acta de matrimonio (si aplica)' =>
                'Solo si el inmueble está en copropiedad conyugal (sociedad conyugal o bienes mancomunados).',
            'Testamento (si aplica)' =>
                'Solo si el propietario heredó el inmueble y el juicio sucesorio ya concluyó.',

            // AVALUO
            'Completar la valuación en el sistema' =>
                'Usa Valor Rápido o Valuación Constructor según el tipo de inmueble.',
            'Vincular la valuación a la captación' =>
                'Así queda ligada a esta captación y visible para el propietario en su Portal.',
            'Presentar la opinión de valor al propietario' =>
                'Apóyate en los comparables reales del Observatorio de Precios de la colonia — datos, no opinión.',
            'Acordar el precio de lista con el propietario' =>
                'Si el propietario duda del precio, muéstrale datos de la zona, no discurso genérico. Un inmueble sobrevaluado se queda sin visitas.',

            // EXCLUSIVA
            'Precio final acordado con el propietario' =>
                'Ya debería estar cerrado desde Avalúo — confírmalo antes de generar el contrato.',
            'Generar el contrato de exclusiva en el sistema' =>
                'Se genera directo desde el sistema con los datos ya capturados en las etapas anteriores.',
            'Enviar el contrato al propietario' =>
                'Envíalo y da seguimiento activo — no lo dejes "esperando a que firme solo".',
            'Obtener la firma del contrato' =>
                'Este es el paso que convierte todo el trabajo anterior en un resultado real.',
            'Explicarle al propietario qué va a pasar después (fotos, publicación, primer reporte)' =>
                'Fechas estimadas de fotos, publicación y primer reporte — esto reduce la duda más común: "¿y si firmo y no hacen nada?".',
            'Invitar al propietario al Portal del Cliente' =>
                'Así el propietario puede seguir el proceso sin tener que llamarte para preguntar.',
        ];

        foreach ($descriptions as $title => $description) {
            DB::table('stage_checklist_templates')
                ->where('operation_type', 'captacion')
                ->where('title', $title)
                ->update(['description' => $description]);
        }
    }

    public function down(): void
    {
        DB::table('stage_checklist_templates')
            ->where('operation_type', 'captacion')
            ->update(['description' => null]);
    }
};
