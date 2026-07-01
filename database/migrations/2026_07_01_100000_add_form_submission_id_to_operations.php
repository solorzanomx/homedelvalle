<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trazabilidad: de qué FormSubmission (lead web) nació esta Operation, para
 * no perder la atribución de campaña/funnel al convertir un lead en
 * captación. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->foreignId('form_submission_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('form_submission_id');
        });
    }
};
