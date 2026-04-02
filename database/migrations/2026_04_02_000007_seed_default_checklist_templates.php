<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $items = [
            // FASE 1: Captacion
            ['both', 'lead', 'Registrar datos del cliente', 1],
            ['both', 'lead', 'Calificar interes y presupuesto', 2],
            ['both', 'lead', 'Asignar prioridad al lead', 3],

            ['both', 'contacto', 'Hacer primera llamada/WhatsApp', 1],
            ['both', 'contacto', 'Enviar informacion de servicios', 2],
            ['both', 'contacto', 'Agendar cita presencial', 3],

            ['both', 'visita', 'Realizar visita a la propiedad', 1],
            ['both', 'visita', 'Tomar fotos y notas de la propiedad', 2],
            ['both', 'visita', 'Presentar analisis comparativo de mercado', 3],
            ['both', 'visita', 'Enviar follow-up post-visita', 4],

            ['both', 'exclusiva', 'Firmar contrato de exclusiva/comision', 1],
            ['both', 'exclusiva', 'Tomar fotos profesionales', 2],
            ['both', 'exclusiva', 'Obtener documentacion de propiedad', 3],
            ['both', 'exclusiva', 'Definir precio/renta de salida', 4],

            // FASE 2: Operacion
            ['both', 'publicacion', 'Publicar en portales inmobiliarios', 1],
            ['both', 'publicacion', 'Crear material de marketing', 2],
            ['both', 'publicacion', 'Publicar en redes sociales', 3],

            ['renta', 'busqueda', 'Identificar candidatos a inquilino', 1],
            ['renta', 'busqueda', 'Pre-calificar candidatos', 2],
            ['renta', 'busqueda', 'Programar visitas con candidatos', 3],
            ['venta', 'busqueda', 'Identificar compradores interesados', 1],
            ['venta', 'busqueda', 'Pre-calificar capacidad de compra', 2],
            ['venta', 'busqueda', 'Programar visitas con compradores', 3],

            ['renta', 'investigacion', 'Solicitar poliza juridica', 1],
            ['renta', 'investigacion', 'Verificar documentos del inquilino', 2],
            ['renta', 'investigacion', 'Esperar aprobacion de investigacion', 3],
            ['venta', 'investigacion', 'Verificar fondos del comprador', 1],
            ['venta', 'investigacion', 'Revisar documentacion legal', 2],
            ['venta', 'investigacion', 'Solicitar avaluo', 3],

            ['both', 'contrato', 'Generar contrato', 1],
            ['both', 'contrato', 'Revision legal del contrato', 2],
            ['both', 'contrato', 'Firmar contrato', 3],

            ['renta', 'entrega', 'Inspeccion del inmueble', 1],
            ['renta', 'entrega', 'Entrega de llaves', 2],
            ['renta', 'entrega', 'Firmar acta de entrega', 3],
            ['venta', 'entrega', 'Firma ante notario', 1],
            ['venta', 'entrega', 'Registro de escritura', 2],
            ['venta', 'entrega', 'Entrega de llaves', 3],

            ['both', 'cierre', 'Cobrar comision', 1],
            ['both', 'cierre', 'Archivar expediente completo', 2],
            ['both', 'cierre', 'Solicitar referidos/testimonio', 3],

            // Post-cierre (renta only)
            ['renta', 'activo', 'Verificar pago de renta mensual', 1],
            ['renta', 'activo', 'Atender reportes de mantenimiento', 2],

            ['renta', 'renovacion', 'Evaluar condiciones de renovacion', 1],
            ['renta', 'renovacion', 'Negociar nuevos terminos', 2],
            ['renta', 'renovacion', 'Firmar renovacion de contrato', 3],
        ];

        $now = now()->toDateTimeString();
        foreach ($items as $item) {
            DB::table('stage_checklist_templates')->insert([
                'operation_type' => $item[0],
                'stage' => $item[1],
                'title' => $item[2],
                'sort_order' => $item[3],
                'is_required' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('stage_checklist_templates')->truncate();
    }
};
