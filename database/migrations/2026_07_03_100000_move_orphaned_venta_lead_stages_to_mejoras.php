<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * VENTA_STAGES dejó de incluir lead/contacto/visita/exclusiva — esas etapas
 * ahora viven solo en CAPTACION_STAGES. Si alguna Operation type=venta se
 * hubiera creado antes en una de esas etapas (vía el formulario manual
 * "+ Nueva Operacion", ya deshabilitado para venta), la movemos a 'mejoras'
 * para que no quede invisible en el kanban filtrado por tipo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('operations')
            ->where('type', 'venta')
            ->whereIn('stage', ['lead', 'contacto', 'visita', 'exclusiva'])
            ->update(['stage' => 'mejoras']);
    }

    public function down(): void
    {
        // No reversible con precisión (no sabemos la etapa original exacta) — sin acción.
    }
};
