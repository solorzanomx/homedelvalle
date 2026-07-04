<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cobros/comisiones que genera un proveedor externo sobre un proceso —
 * mismo patrón que Referral (tercero externo que cobra/comisiona sobre
 * una Operation), pero dual-FK como PolizaJuridica (puede colgar de una
 * Operation activa o de un RentalProcess ya cerrado). status usa string
 * en vez de enum de BD a propósito: el enum de "referrals" quedó
 * desincronizado del vocabulario real usado en código (pending/approved/
 * paid en BD vs registrado/en_proceso/por_pagar/pagado en código) — se
 * evita ese mismo error aquí desde el inicio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('rental_process_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('provider_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_contact_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flow', 10); // cargo (nos cobra) | comision (nos comisiona)
            $table->string('service_description');
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->string('status', 20)->default('registrado'); // registrado, confirmado, liquidado
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_charges');
    }
};
