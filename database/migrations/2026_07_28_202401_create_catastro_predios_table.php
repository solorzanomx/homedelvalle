<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catastro (predial) de Benito Juárez — base pública independiente, sin
 * relación con Property. calle_numero se parte en calle/numero al importar
 * para poder buscar y, más adelante, cruzar por dirección con
 * zonificacion_predios (los formatos de calle no coinciden 1 a 1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catastro_predios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fid')->nullable();
            $table->unsignedBigInteger('fid_2')->nullable();
            $table->string('calle_numero')->nullable();
            $table->string('calle')->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('colonia')->nullable();
            $table->string('alcaldia')->nullable();
            $table->decimal('sup_terreno', 12, 2)->nullable();
            $table->decimal('sup_construccion', 12, 2)->nullable();
            $table->unsignedSmallInteger('anio_construccion')->nullable();
            $table->string('instal_esp', 20)->nullable();
            $table->decimal('valor_unitario_suelo', 14, 2)->nullable();
            $table->decimal('valor_suelo', 16, 2)->nullable();
            $table->string('cve_vus', 30)->nullable();
            $table->string('subsidio', 50)->nullable();
            $table->timestamps();

            $table->index('colonia');
            $table->index('calle');
            $table->index('sup_terreno');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catastro_predios');
    }
};
