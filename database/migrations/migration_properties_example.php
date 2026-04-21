<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN DE EJEMPLO - TABLA PROPERTIES
 *
 * Nombre del archivo: YYYY_MM_DD_HHMMSS_create_properties_table.php
 * php artisan make:migration create_properties_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tipo_propiedad')->nullable(); // Casa, Depto, Terreno, etc.
            $table->enum('operacion', ['venta', 'renta', 'ambas'])->default('venta');

            // Ubicación
            $table->string('colonia')->nullable();
            $table->string('alcaldia')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('direccion')->nullable();

            // Precio
            $table->decimal('precio', 15, 2)->nullable();
            $table->string('moneda')->default('MXN');

            // Dimensiones
            $table->decimal('terreno_m2', 10, 2)->nullable();
            $table->decimal('construccion_m2', 10, 2)->nullable();

            // Espacios
            $table->integer('recamaras')->nullable();
            $table->integer('baños')->nullable();
            $table->integer('medios_baños')->nullable();
            $table->integer('estacionamientos')->nullable();

            // Características
            $table->string('antigüedad')->nullable(); // "Nuevo", "10 años", etc.
            $table->string('nivel')->nullable(); // "PB", "1er Piso", etc.
            $table->string('uso_suelo')->nullable();
            $table->string('estado_conservacion')->nullable(); // Excelente, Bueno, Regular, etc.
            $table->string('estatus_legal')->nullable();

            // Descripción
            $table->longText('descripcion')->nullable();
            $table->json('amenidades')->nullable(); // Mejor como JSON
            $table->longText('observaciones')->nullable();

            // Multimedia
            $table->string('qr_path')->nullable(); // storage/properties/{id}/qr/qr-code.png

            // Relaciones
            $table->unsignedBigInteger('user_id')->nullable(); // Broker/Owner
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Metadata
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
