<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4 del prompt de leads del blog: parámetros de la calculadora de costo de sucesión, editables
 * en /admin/succession-calculator. NINGUNA cifra aquí está validada por un notario — todo el seed
 * queda marcado `validated=false` a propósito (ver docs/funcionalidades/blog-calculadora-sucesion.md,
 * sección "Pendiente de validar").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('succession_calculator_configs', function (Blueprint $table) {
            $table->id();
            $table->string('scenario', 20)->unique();   // 'con_testamento' | 'sin_testamento'
            $table->decimal('notarial_pct_min', 5, 2);   // % del valor: trámite notarial (con testamento) o juicio sucesorio (sin testamento)
            $table->decimal('notarial_pct_max', 5, 2);
            $table->decimal('isai_pct_min', 5, 2);       // Impuesto Sobre Adquisición de Inmuebles — CDMX suele exentar/reducir a herederos directos, hay que confirmarlo
            $table->decimal('isai_pct_max', 5, 2);
            $table->unsignedInteger('registro_flat_min');   // Registro Público de la Propiedad, pesos
            $table->unsignedInteger('registro_flat_max');
            $table->unsignedInteger('avaluo_flat_min');     // Avalúo, pesos
            $table->unsignedInteger('avaluo_flat_max');
            $table->unsignedInteger('otros_flat_min');      // Edictos, gestoría, copias certificadas…
            $table->unsignedInteger('otros_flat_max');
            $table->unsignedInteger('extra_heredero_flat')->default(0);   // por cada heredero adicional al primero
            $table->unsignedInteger('sin_escrituras_extra_min')->default(0);   // si el inmueble no está escriturado a nombre del difunto
            $table->unsignedInteger('sin_escrituras_extra_max')->default(0);
            $table->unsignedTinyInteger('tiempo_min_meses');
            $table->unsignedTinyInteger('tiempo_max_meses');
            $table->boolean('validated')->default(false);   // true solo cuando Alejandro lo confirme con el notario
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Placeholders de referencia general (NO cotización, NO validados) — estructura correcta,
        // cifras a confirmar. Ver la lista completa en la documentación de la fase.
        DB::table('succession_calculator_configs')->insert([
            [
                'scenario' => 'con_testamento',
                'notarial_pct_min' => 4, 'notarial_pct_max' => 6,
                'isai_pct_min' => 0, 'isai_pct_max' => 2,
                'registro_flat_min' => 3000, 'registro_flat_max' => 8000,
                'avaluo_flat_min' => 3000, 'avaluo_flat_max' => 6000,
                'otros_flat_min' => 2000, 'otros_flat_max' => 5000,
                'extra_heredero_flat' => 1500,
                'sin_escrituras_extra_min' => 0, 'sin_escrituras_extra_max' => 0,
                'tiempo_min_meses' => 2, 'tiempo_max_meses' => 4,
                'validated' => false,
                'notes' => 'PENDIENTE VALIDAR con notario. Placeholder inicial, no cotización real.',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'scenario' => 'sin_testamento',
                'notarial_pct_min' => 6, 'notarial_pct_max' => 9,
                'isai_pct_min' => 0, 'isai_pct_max' => 2,
                'registro_flat_min' => 3000, 'registro_flat_max' => 8000,
                'avaluo_flat_min' => 3000, 'avaluo_flat_max' => 6000,
                'otros_flat_min' => 4000, 'otros_flat_max' => 12000,
                'extra_heredero_flat' => 2500,
                'sin_escrituras_extra_min' => 8000, 'sin_escrituras_extra_max' => 25000,
                'tiempo_min_meses' => 6, 'tiempo_max_meses' => 12,
                'validated' => false,
                'notes' => 'PENDIENTE VALIDAR con notario. Placeholder inicial, no cotización real. Si hay conflicto entre herederos puede volverse juicio (más tiempo/costo, fuera de este rango).',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_calculator_configs');
    }
};
