<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adéndum al Contrato de Comisión Mercantil — registra formalmente al
 * comprador de una PurchaseOffer, su oferta/forma de pago y ratifica la
 * comisión de HDV. Esquema de comisión: exhibición única a escrituras, o
 * proporcional a los pagos del comprador (regla de negocio: si el anticipo
 * rebasa ~20-30% del precio, se cobra la misma proporción de la comisión a
 * la firma del contrato). Ver docs de referencia en el plan / memoria.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_offer_addendums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_offer_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('numero')->default(1);
            // "Contrato de Comisión Mercantil" (contratos viejos) o
            // "Acuerdo de Representación" (los actuales) — texto libre.
            $table->string('contrato_nombre')->default('Contrato de Comisión Mercantil');
            $table->date('contrato_fecha');
            $table->decimal('comision_amount', 14, 2);
            $table->string('comision_esquema', 20)->default('exhibicion_unica'); // exhibicion_unica | proporcional
            $table->decimal('comision_firma_contrato', 14, 2)->nullable();
            $table->decimal('comision_firma_escritura', 14, 2)->nullable();
            $table->foreignId('representative_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_offer_addendums');
    }
};
