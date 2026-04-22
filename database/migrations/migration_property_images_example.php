<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRACIÓN DE EJEMPLO - TABLA PROPERTY_IMAGES
 *
 * Almacena las imágenes relacionadas a cada propiedad
 * php artisan make:migration create_property_images_table
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('property_images')) {
            return;
        }

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();

            // Relación con propiedad
            $table->unsignedBigInteger('property_id');
            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
                ->onDelete('cascade');

            // Datos de imagen
            $table->string('path'); // storage/properties/{id}/images/image.jpg
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();

            // Metadata
            $table->integer('order')->default(0); // Para ordenar galería
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false); // Primera imagen (hero)

            $table->timestamps();

            // Índices
            $table->index('property_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_images');
    }
};
