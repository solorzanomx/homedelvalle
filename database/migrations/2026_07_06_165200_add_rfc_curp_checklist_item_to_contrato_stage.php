<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hoy un cliente puede llegar a "Firmar contrato" sin RFC/CURP capturados —
 * ambos campos son nullable y ningún checklist los exigía (auditoría
 * 2026-07-06). Con el bloqueo de avance manual ya activo
 * (OperationChecklistService::hasIncompleteRequiredItems, usado en
 * OperationController::updateStage()), agregar este ítem ya alcanza para
 * impedir el avance sin ese dato.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('stage_checklist_templates')->insert([
            'operation_type' => 'both',
            'stage' => 'contrato',
            'title' => 'RFC y CURP del cliente capturados',
            'sort_order' => 0,
            'is_required' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('stage_checklist_templates')
            ->where('operation_type', 'both')
            ->where('stage', 'contrato')
            ->where('title', 'RFC y CURP del cliente capturados')
            ->delete();
    }
};
