<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vínculo bidireccional entre la Operation (type=renta) de la que nace un
 * RentalProcess al cerrarse — mismo patrón ya usado por
 * Operation.source_operation_id para Captación→Venta/Renta. Sin esto no
 * había forma de conectar "Colocación Activa" con "Post-Cierre".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('operation_id');
        });
    }
};
