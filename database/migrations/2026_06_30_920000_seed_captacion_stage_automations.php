<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Notifica automáticamente al propietario por WhatsApp cada vez que su
 * captación avanza a una etapa clave del proceso — ver docs/07-FLUJO-
 * CAPTACION-Y-MEJORAS.md sección 4 ("Transversal - cliente"). Usa el motor
 * de automatizaciones ya existente (Automation + AutomationStep,
 * trigger_type=stage_change), sin código nuevo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('automations')
            ->where('name', 'like', 'Captación — avance de etapa:%')
            ->delete();

        $stages = [
            'contacto' => 'Hola {{nombre}}, ya estamos trabajando en tu captación con Home del Valle — en breve recibirás la presentación de nuestros servicios.',
            'visita'   => 'Hola {{nombre}}, agendamos la visita a tu inmueble. Cualquier duda antes de la cita, contáctanos.',
            'avaluo'   => 'Hola {{nombre}}, ya tenemos avance en la valuación de tu inmueble. Tu asesor te compartirá los resultados en breve.',
            'exclusiva'=> 'Hola {{nombre}}, ¡gracias por tu confianza! Tu Acuerdo de Representación está en proceso — pronto comenzamos con fotos y publicación.',
        ];

        $now = now();

        foreach ($stages as $stage => $message) {
            $automationId = DB::table('automations')->insertGetId([
                'name'             => "Captación — avance de etapa: {$stage}",
                'description'      => "Notifica por WhatsApp al propietario cuando su captación avanza a la etapa '{$stage}'.",
                'trigger_type'     => 'stage_change',
                'trigger_config'   => json_encode(['operation_type' => 'captacion', 'to_stage' => $stage]),
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
                'config'        => json_encode(['message' => $message]),
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('automations')
            ->where('name', 'like', 'Captación — avance de etapa:%')
            ->delete();
    }
};
