<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Salud de URLs del blog (docs/funcionalidades/blog-redirects.md). Slugs muertos → 301/410 en vez de 404 seco. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 255);   // ej. /blog/cuanto-sucesion-cdmx-2026 (normalizado: minúsculas, sin barra final)
            $table->string('to_path', 255);     // destino relativo, ej. /blog/cuanto-cuesta-sucesion-cdmx-2026
            $table->unsignedSmallInteger('status')->default(301);   // 301 = redirige, 410 = ya no existe (se fue a propósito)
            $table->boolean('active')->default(true);   // Fase 6: fusiones dejan redirects preparados pero inactivos
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->string('notes', 255)->nullable();   // 'auto' = lo creó el fallback por similitud o el cambio de slug
            $table->timestamps();

            $table->unique('from_path');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_redirects');
    }
};
