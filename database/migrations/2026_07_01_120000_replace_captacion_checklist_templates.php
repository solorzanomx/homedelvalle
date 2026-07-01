<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reemplaza el checklist de captación por la versión revisada junto al
 * usuario, alineada con docs/08-MANUAL-BROKER-CAPTACION.md. Dos ajustes
 * de fondo sobre lo que había: (1) "agendar visita" estaba duplicado
 * entre LEAD y CONTACTO — queda solo en CONTACTO; (2) el checklist de
 * revision_docs pedía documentos (escritura, boleta predial) que no
 * coinciden con lo que la ficha realmente pide para subir
 * (Captacion::REQUIRED_DOCS_ETAPA1 = identificación, CURP, comprobante de
 * domicilio) — ya quedó alineado a eso.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('stage_checklist_templates')
            ->where('operation_type', 'captacion')
            ->whereIn('stage', ['lead', 'contacto', 'visita', 'revision_docs', 'avaluo', 'exclusiva'])
            ->delete();

        $now = now()->toDateTimeString();

        $items = [
            // LEAD
            ['lead', 'Llamar al propietario (objetivo: menos de 1 hora desde que llega el lead)', true, 1],
            ['lead', 'Confirmar interés real y motivo (vender/rentar, por qué, qué tanta prisa tiene)', true, 2],
            ['lead', 'Registrar datos básicos del inmueble (dirección, tipo, m² aproximados)', true, 3],

            // CONTACTO
            ['contacto', 'Enviar Presentación de Home del Valle (email o WhatsApp)', true, 1],
            ['contacto', 'Agendar la visita', true, 2],
            ['contacto', 'Confirmar que el propietario recibió/vio la Presentación', false, 3],

            // VISITA
            ['visita', 'Confirmar la visita con el propietario (día antes)', true, 1],
            ['visita', 'Realizar la visita y documentar el estado del inmueble (fotos, notas)', true, 2],
            ['visita', 'Dar la Opinión de Valor en el momento (Valor Rápido o Valuación Constructor)', true, 3],
            ['visita', 'Presentar la Propuesta de Servicios ahí mismo (modo "en vivo")', true, 4],
            ['visita', 'Ofrecer firmar la exclusiva en el momento si el propietario está listo', false, 5],

            // REVISION_DOCS
            ['revision_docs', 'Identificación oficial', true, 1],
            ['revision_docs', 'CURP', true, 2],
            ['revision_docs', 'Comprobante de domicilio', true, 3],
            ['revision_docs', 'Acta de matrimonio (si aplica)', false, 4],
            ['revision_docs', 'Testamento (si aplica)', false, 5],

            // AVALUO
            ['avaluo', 'Completar la valuación en el sistema', true, 1],
            ['avaluo', 'Vincular la valuación a la captación', true, 2],
            ['avaluo', 'Presentar la opinión de valor al propietario', true, 3],
            ['avaluo', 'Acordar el precio de lista con el propietario', true, 4],

            // EXCLUSIVA
            ['exclusiva', 'Precio final acordado con el propietario', true, 1],
            ['exclusiva', 'Generar el contrato de exclusiva en el sistema', true, 2],
            ['exclusiva', 'Enviar el contrato al propietario', true, 3],
            ['exclusiva', 'Obtener la firma del contrato', true, 4],
            ['exclusiva', 'Explicarle al propietario qué va a pasar después (fotos, publicación, primer reporte)', true, 5],
            ['exclusiva', 'Invitar al propietario al Portal del Cliente', false, 6],
        ];

        foreach ($items as [$stage, $title, $required, $order]) {
            DB::table('stage_checklist_templates')->insert([
                'operation_type' => 'captacion',
                'stage' => $stage,
                'title' => $title,
                'sort_order' => $order,
                'is_required' => $required,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // No se restaura el contenido anterior — ver
        // 2026_06_30_910000_seed_captacion_checklist_templates.php como
        // referencia histórica si hiciera falta reconstruirlo.
    }
};
