<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consecuencia necesaria de mover mejoras/fotos_video/carpeta_lista de
 * CAPTACION_STAGES a VENTA_STAGES/RENTA_STAGES (ver Operation.php): los
 * ítems de checklist sembrados para esas 3 etapas seguían etiquetados
 * operation_type='captacion', pero ahora viven en Operations type=venta/
 * renta (la Operation spawneada tras firmar la exclusiva). Sin este fix,
 * StageChecklistTemplate::forStage() nunca los encuentra para esas
 * Operations y el checklist sale vacío. Ver docs/07-FLUJO-CAPTACION-Y-
 * MEJORAS.md y memoria de proyecto.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('stage_checklist_templates')
            ->whereIn('stage', ['mejoras', 'fotos_video', 'carpeta_lista'])
            ->where('operation_type', 'captacion')
            ->update(['operation_type' => 'both']);
    }

    public function down(): void
    {
        DB::table('stage_checklist_templates')
            ->whereIn('stage', ['mejoras', 'fotos_video', 'carpeta_lista'])
            ->where('operation_type', 'both')
            ->update(['operation_type' => 'captacion']);
    }
};
