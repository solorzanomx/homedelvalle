<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Checklist del pipeline de Inquilinos, etapas Precalificación→Listo —
 * nunca se sembró para 'inquilino' cuando se separó el pipeline (commit
 * 376f00a), así que OperationChecklistService::checkAndAutoAdvance()
 * saltaba directo a 'listo' sin pedir identificación/ingresos/presupuesto.
 * Lead/Contacto/Visita NO se siembran aquí a propósito: ya heredan los
 * ítems genéricos operation_type='both' (compartidos con renta/comprador);
 * agregar también entradas 'inquilino' para esas 3 etapas duplicaría los
 * ítems en el checklist (StageChecklistTemplate::scopeForStage() hace
 * whereIn([$type,'both']), no un fallback exclusivo — mismo patrón ya
 * usado por 'comprador' en 2026_07_03_170000_seed_comprador_checklist_
 * templates.php, redundancia preexistente que no se replica aquí).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $items = [
            // Precalificacion
            ['stage' => 'precalificacion', 'title' => 'Identificación oficial', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'CURP', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'Comprobante de ingresos', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'Confirmar presupuesto de renta real con el inquilino', 'required' => true],
            ['stage' => 'precalificacion', 'title' => 'Verificar garantía disponible (aval, depósito o póliza)', 'required' => false],
            // Listo
            ['stage' => 'listo', 'title' => 'Confirmar que está listo para rentar', 'required' => true],
            ['stage' => 'listo', 'title' => 'Explicarle el proceso de firma del contrato de arrendamiento', 'required' => true],
        ];

        foreach ($items as $i => $item) {
            DB::table('stage_checklist_templates')->insert([
                'operation_type' => 'inquilino',
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
        DB::table('stage_checklist_templates')->where('operation_type', 'inquilino')->delete();
    }
};
