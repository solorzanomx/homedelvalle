<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Notifica automáticamente al propietario por WhatsApp en cuanto su
 * propiedad llega a la etapa 'publicacion' del pipeline de venta — mismo
 * patrón y motor que 2026_06_30_920000_seed_captacion_stage_automations.php
 * (Automation + trigger_type=stage_change), sin código nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('automations')
            ->where('name', 'Venta — inmueble publicado')
            ->delete();

        $now = now();

        $automationId = DB::table('automations')->insertGetId([
            'name'             => 'Venta — inmueble publicado',
            'description'      => 'Notifica por WhatsApp al propietario cuando su Operation de venta llega a la etapa "publicacion".',
            'trigger_type'     => 'stage_change',
            'trigger_config'   => json_encode(['operation_type' => 'venta', 'to_stage' => 'publicacion']),
            'is_active'        => true,
            'allow_reentry'    => true,
            'enrollment_count' => 0,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        DB::table('automation_steps')->insert([
            'automation_id' => $automationId,
            'position'      => 1,
            'type'          => 'send_whatsapp',
            'config'        => json_encode([
                'message' => '¡Hola {{nombre}}! Tu propiedad ya está publicada y visible para compradores. Puedes ver el avance completo de tu venta en tu portal: https://miportal.homedelvalle.mx',
            ]),
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('automations')
            ->where('name', 'Venta — inmueble publicado')
            ->delete();
    }
};
