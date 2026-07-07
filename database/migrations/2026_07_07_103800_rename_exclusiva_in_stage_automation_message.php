<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El mensaje de WhatsApp automático al entrar a stage='exclusiva'
 * (2026_06_30_920000_seed_captacion_stage_automations.php) ya corrió en
 * producción — el mensaje real vive en automation_steps.config (JSON),
 * no en el archivo de seed. Cambiar el seed viejo no actualiza lo ya
 * sembrado; se actualiza el dato directo.
 */
return new class extends Migration
{
    private const OLD_MESSAGE = 'Hola {{nombre}}, ¡gracias por tu confianza! Tu contrato de exclusiva está en proceso — pronto comenzamos con fotos y publicación.';
    private const NEW_MESSAGE = 'Hola {{nombre}}, ¡gracias por tu confianza! Tu Acuerdo de Representación está en proceso — pronto comenzamos con fotos y publicación.';

    public function up(): void
    {
        $automationId = DB::table('automations')
            ->where('name', 'Captación — avance de etapa: exclusiva')
            ->value('id');

        if (!$automationId) {
            return;
        }

        $step = DB::table('automation_steps')
            ->where('automation_id', $automationId)
            ->where('type', 'send_whatsapp')
            ->first();

        if (!$step) {
            return;
        }

        $config = json_decode($step->config, true);
        if (($config['message'] ?? null) === self::OLD_MESSAGE) {
            $config['message'] = self::NEW_MESSAGE;
            DB::table('automation_steps')->where('id', $step->id)->update(['config' => json_encode($config)]);
        }
    }

    public function down(): void
    {
        $automationId = DB::table('automations')
            ->where('name', 'Captación — avance de etapa: exclusiva')
            ->value('id');

        if (!$automationId) {
            return;
        }

        $step = DB::table('automation_steps')
            ->where('automation_id', $automationId)
            ->where('type', 'send_whatsapp')
            ->first();

        if (!$step) {
            return;
        }

        $config = json_decode($step->config, true);
        if (($config['message'] ?? null) === self::NEW_MESSAGE) {
            $config['message'] = self::OLD_MESSAGE;
            DB::table('automation_steps')->where('id', $step->id)->update(['config' => json_encode($config)]);
        }
    }
};
