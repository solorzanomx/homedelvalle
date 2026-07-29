<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zonificación de SEDUVI (uso de suelo, niveles permitidos, coordenadas) —
 * base pública independiente, sin relación con Property. Sirve para buscar
 * predios candidatos a campaña (ej. H4 + más de 300m²) y, más adelante, como
 * dato de referencia al dar de alta propiedades o hacer valuaciones.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonificacion_predios', function (Blueprint $table) {
            $table->id();
            $table->string('alcaldia');
            $table->string('calle')->nullable();
            $table->string('no_externo')->nullable();
            $table->string('colonia')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->decimal('superficie', 12, 2)->nullable();
            $table->string('uso_descri')->nullable();
            $table->string('densidad_d')->nullable();
            $table->string('niveles', 20)->nullable();
            $table->string('altura', 20)->nullable();
            $table->string('area_libre', 20)->nullable();
            $table->string('minimo_viv', 20)->nullable();
            $table->text('liga_ciudadana')->nullable();
            $table->string('cuenta_catastral')->nullable();
            $table->decimal('longitud', 12, 8)->nullable();
            $table->decimal('latitud', 12, 8)->nullable();
            $table->timestamps();

            $table->index('colonia');
            $table->index('calle');
            $table->index('niveles');
            $table->index('cuenta_catastral');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zonificacion_predios');
    }
};
