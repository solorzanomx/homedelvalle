<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro genérico de aperturas de correo (pixel 1x1) para cualquier envío
 * hecho vía CustomEmailTemplate::send(). trackable es opcional: cuando el
 * llamador pasa un modelo (Broker, Client, FormSubmission...) se guarda aquí
 * para poder correlacionar, y además se denormaliza en el propio registro
 * (ej. brokers.email_opened_at) para mostrarlo sin joins en listas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_opens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('custom_email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->nullableMorphs('trackable');
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('opens_count')->default(0);
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_opens');
    }
};
