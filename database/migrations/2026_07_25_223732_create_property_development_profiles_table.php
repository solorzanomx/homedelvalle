<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos técnicos de un predio para análisis de potencial de desarrollo
 * (VRC) — separado de Property porque solo aplica a una fracción de los
 * inmuebles (terrenos y casas con potencial de desarrollo), no a todo el
 * inventario residencial normal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_development_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->unique()->constrained()->cascadeOnDelete();

            // Dimensiones
            $table->decimal('frente', 8, 2)->nullable(); // metros
            $table->decimal('fondo', 8, 2)->nullable();  // metros
            $table->string('forma_terreno')->nullable(); // rectangular, irregular, trapezoidal, en_escuadra, otro

            // Zonificación / SEDUVI
            $table->string('uso_suelo')->nullable();       // código crudo, ej. "H4/20/Z"
            $table->string('zonificacion_key')->nullable(); // clave de ConstructorValuationService::ZONIFICACIONES
            $table->decimal('cos', 5, 2)->nullable();
            $table->decimal('cus', 5, 2)->nullable();
            $table->unsignedTinyInteger('niveles_permitidos')->nullable();

            // Situación legal y física
            $table->text('restricciones')->nullable();     // alineamiento, restricción al frente, servidumbres
            $table->text('colindancias')->nullable();
            $table->text('servicios')->nullable();          // agua, drenaje, luz — texto libre
            $table->boolean('libre_gravamen')->nullable();
            $table->text('situacion_legal')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_development_profiles');
    }
};
