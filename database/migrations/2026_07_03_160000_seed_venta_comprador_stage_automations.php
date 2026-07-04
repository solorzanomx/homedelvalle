<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Notifica por WhatsApp al COMPRADOR (secondaryClient de la Operation) en
 * cada etapa post-oferta-aceptada — mismo motor de automatizaciones ya usado
 * para el vendedor (2026_06_30_920000_seed_captacion_stage_automations.php,
 * 2026_07_03_110000_seed_venta_publicacion_automation.php), pero con
 * operation_type='venta_comprador' (sufijo agregado en
 * OperationChecklistService::changeStage() al notificar al comprador) para
 * no cruzarse con las automatizaciones ya existentes del vendedor, que usan
 * 'venta' a secas sobre los mismos to_stage.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $automations = [
            [
                'name'     => 'Comprador — oferta aceptada',
                'to_stage' => 'oferta_aceptada',
                'message'  => '¡Felicidades {{nombre}}! Tu oferta fue aceptada. Sigue tu proceso de compra en tu portal: https://miportal.homedelvalle.mx',
            ],
            [
                'name'     => 'Comprador — en investigación',
                'to_stage' => 'investigacion',
                'message'  => 'Hola {{nombre}}, estamos verificando tu documentación y fondos para continuar con tu compra.',
            ],
            [
                'name'     => 'Comprador — contrato en preparación',
                'to_stage' => 'contrato',
                'message'  => 'Hola {{nombre}}, tu contrato de compraventa está en preparación. Te avisaremos cuando esté listo para firmar.',
            ],
            [
                'name'     => 'Comprador — preparando entrega',
                'to_stage' => 'entrega',
                'message'  => 'Hola {{nombre}}, ya casi — estamos coordinando la firma ante notario y la entrega de tu nuevo inmueble.',
            ],
            [
                'name'     => 'Comprador — compra cerrada',
                'to_stage' => 'cierre',
                'message'  => '¡Felicidades {{nombre}}! Tu compra se cerró con éxito. Gracias por confiar en Home del Valle.',
            ],
        ];

        foreach ($automations as $a) {
            DB::table('automations')->where('name', $a['name'])->delete();

            $automationId = DB::table('automations')->insertGetId([
                'name'             => $a['name'],
                'description'      => 'Notifica por WhatsApp al comprador cuando su Operation llega a la etapa "' . $a['to_stage'] . '".',
                'trigger_type'     => 'stage_change',
                'trigger_config'   => json_encode(['operation_type' => 'venta_comprador', 'to_stage' => $a['to_stage']]),
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
                'config'        => json_encode(['message' => $a['message']]),
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('automations')->whereIn('name', [
            'Comprador — oferta aceptada',
            'Comprador — en investigación',
            'Comprador — contrato en preparación',
            'Comprador — preparando entrega',
            'Comprador — compra cerrada',
        ])->delete();
    }
};
