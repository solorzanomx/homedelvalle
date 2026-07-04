<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Empresas externas que dan servicio (notarías, aseguradoras de pólizas
 * jurídicas, limpieza, mantenimiento, etc.) — distinto de BrokerCompany
 * (inmobiliarias aliadas). Mismo patrón de esquema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 30); // notaria, poliza_juridica, limpieza, mantenimiento, fotografia_video, legal, contabilidad, otro
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_companies');
    }
};
