<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Checklist del pipeline de Compradores (Lead→Contacto→Visita→Precalificación
 * →Listo) — mismo mecanismo que el checklist de Captación
 * (2026_06_30_920000_seed_captacion_stage_automations.php /
 * 2026_07_01_120000_replace_captacion_checklist_templates.php), sin código
 * nuevo, solo datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $items = [
            // Lead
            ['stage' => 'lead', 'title' => 'Contactar al comprador en menos de 1 hora', 'required' => true],
            ['stage' => 'lead', 'title' => 'Entender qué busca (zona, presupuesto, tipo de inmueble)', 'required' => true],
            ['stage' => 'lead', 'title' => 'Registrar datos básicos de contacto', 'required' => true],
            // Contacto
            ['stage' => 'contacto', 'title' => 'Enviar propiedades candidatas', 'required' => true],
            ['stage' => 'contacto', 'title' => 'Agendar primera visita', 'required' => true],
            // Visita
            ['stage' => 'visita', 'title' => 'Confirmar visita un día antes', 'required' => true],
            ['stage' => 'visita', 'title' => 'Realizar recorrido y recabar feedback', 'required' => true],
            // Precalificacion
            ['stage' => 'precalificacion', 'title' => 'Identificación oficial', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'CURP', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'Comprobante de ingresos', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'Carta de preautorización de crédito', 'required' => false],
            ['stage' => 'precalificacion', 'title' => 'Confirmar presupuesto real con el comprador', 'required' => true],
            // Listo
            ['stage' => 'listo', 'title' => 'Confirmar que está listo para ofertar', 'required' => true],
            ['stage' => 'listo', 'title' => 'Explicarle el proceso de oferta formal', 'required' => true],
        ];

        foreach ($items as $i => $item) {
            DB::table('stage_checklist_templates')->insert([
                'operation_type' => 'comprador',
                'stage'          => $item['stage'],
                'title'          => $item['title'],
                'sort_order'     => $i,
                'is_required'    => $item['required'],
                'is_active'      => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('stage_checklist_templates')->where('operation_type', 'comprador')->delete();
    }
};
