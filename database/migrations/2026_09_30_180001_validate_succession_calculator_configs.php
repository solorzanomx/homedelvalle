<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Alejandro confirmó las cifras de los 2 escenarios (con/sin testamento) de la calculadora de
 * costo de sucesión — ya no son placeholder. Marca `validated=true` en los 2 registros de
 * `succession_calculator_configs`, lo que quita el aviso "⚠ Estimación de referencia, todavía no
 * confirmada con un notario" que se mostraba a cada persona justo después de dejar su WhatsApp
 * (ver docs/funcionalidades/blog-calculadora-sucesion.md).
 *
 * Las cifras en sí (los %/montos por escenario) NO se tocan aquí — son admin-editables en
 * /admin/succession-calculator y ya reflejan lo que Alejandro confirmó.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('succession_calculator_configs')
            ->whereIn('scenario', ['con_testamento', 'sin_testamento'])
            ->update([
                'validated' => true,
                'notes' => 'Validado por Alejandro con notario (2026-09-28).',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('succession_calculator_configs')
            ->whereIn('scenario', ['con_testamento', 'sin_testamento'])
            ->update([
                'validated' => false,
                'notes' => 'PENDIENTE VALIDAR con notario. Placeholder inicial, no cotización real.',
                'updated_at' => now(),
            ]);
    }
};
