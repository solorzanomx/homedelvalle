<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unifica el rastreo de correos: antes CustomEmailTemplate::send() creaba un
 * EmailOpen aparte (token + trackable genérico) para brokers/cumpleaños, sin
 * relación con el log de Mensajes que ya usan las automatizaciones de
 * clientes. Ahora todo pasa por Message: client_id sigue funcionando igual
 * para las automatizaciones existentes, y trackable_type/id (nullable)
 * permite ligar el mensaje a un FormSubmission o Broker cuando no hay
 * Client todavía. external_id se reutiliza como token del pixel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->nullableMorphs('trackable');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable(false)->change();
            $table->dropMorphs('trackable');
        });
    }
};
