<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El CTA del blog por cluster (Fase 3 del prompt de leads) captura solo WhatsApp — sin email — y
 * `email` era NOT NULL desde que se creó la tabla (todos los formularios anteriores lo exigían).
 * Mismo criterio ya usado en `clients.email` (migración 2026_05_27_100003_make_clients_email_nullable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Irreversible sin decidir qué poner en las filas que quedaron sin email — down() intencional no-op.
    }
};
