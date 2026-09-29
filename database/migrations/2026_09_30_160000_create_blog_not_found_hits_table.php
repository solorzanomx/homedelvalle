<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 7 del prompt de leads del blog: "los 404 más frecuentes" en el panel de admin. Antes, un
 * slug de /blog/* que no encontraba ni un match difuso simplemente rendía la vista 404 sin dejar
 * rastro — no había cómo saber qué buscaba la gente. Esta tabla lo registra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_not_found_hits', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_not_found_hits');
    }
};
