<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Salida automática por actividad real (2026-10-06): una automatización de "sin respuesta" seguía
 * su cadena ciega por tiempo aunque el cliente ya tuviera actividad real (trato activo, interacción
 * reciente) — caso real: a Yarlin Nava le llegó "¿Seguimos en contacto?" en plena negociación activa
 * de su renta. Opt-in explícito por automatización (default false): solo las que de verdad son
 * "chasing a alguien que no contesta" deben cancelarse solas al detectar actividad.
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('automations', function (Blueprint $table) {
            if (! Schema::hasColumn('automations', 'exit_on_engagement')) {
                $table->boolean('exit_on_engagement')->default(false)->comment('Cancela la inscripción sola si el cliente muestra actividad real (trato activo o interacción reciente) antes de ejecutar el siguiente paso');
            }
        });
    }

    public function down(): void {
        Schema::table('automations', function (Blueprint $table) {
            if (Schema::hasColumn('automations', 'exit_on_engagement')) {
                $table->dropColumn('exit_on_engagement');
            }
        });
    }
};
