<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sin esto, un paso send_email/send_whatsapp fallido se reintentaba cada
 * minuto para siempre (AutomationEngine::executeStep() nunca tocaba
 * next_run_at/status en el camino de fallo) — bug real encontrado en la
 * auditoría 2026-07-04. attempts cuenta intentos del paso ACTUAL (se
 * resetea al avanzar de paso); failed_at marca cuándo se agotaron los
 * reintentos (status pasa a 'failed', ver AutomationEnrollment::markFailed()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('automation_enrollments', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempts')->default(0)->after('current_step');
            $table->timestamp('failed_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('automation_enrollments', function (Blueprint $table) {
            $table->dropColumn(['attempts', 'failed_at']);
        });
    }
};
