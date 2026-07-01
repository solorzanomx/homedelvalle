<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * action_type identifica qué mini-acción embebida debe renderizar la
 * "cabina de etapa" (App\Livewire\Admin\CaptacionStageCockpit) para este
 * ítem, en vez de un checkbox manual suelto. null = checkbox manual de
 * siempre (default para todo lo que no se haya diseñado todavía). Ver
 * docs/07-FLUJO-CAPTACION-Y-MEJORAS.md y memoria de proyecto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stage_checklist_templates', function (Blueprint $table) {
            $table->string('action_type')->nullable()->after('title');
        });

        $map = [
            'Llamar al propietario (objetivo: menos de 1 hora desde que llega el lead)' => 'llamar',
            'Confirmar interés real y motivo (vender/rentar, por qué, qué tanta prisa tiene)' => 'confirmar_interes',
            'Registrar datos básicos del inmueble (dirección, tipo, m² aproximados)' => 'datos_inmueble',
        ];

        foreach ($map as $title => $actionType) {
            DB::table('stage_checklist_templates')
                ->where('operation_type', 'captacion')
                ->where('stage', 'lead')
                ->where('title', $title)
                ->update(['action_type' => $actionType]);
        }
    }

    public function down(): void
    {
        Schema::table('stage_checklist_templates', function (Blueprint $table) {
            $table->dropColumn('action_type');
        });
    }
};
