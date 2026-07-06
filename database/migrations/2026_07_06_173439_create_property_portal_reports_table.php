<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Metricas semanales de portales externos (Inmuebles24 por ahora, sin API —
 * se cargan a mano desde un Excel que el broker descarga cada semana por
 * propiedad). unique(property_id, portal, week_start) es la clave del
 * upsert: cada descarga trae el historial completo hasta la fecha, asi que
 * re-importar el archivo actualiza semanas existentes en vez de duplicarlas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_portal_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('portal', 30);
            $table->string('external_listing_id')->nullable();
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedInteger('exposicion')->default(0);
            $table->unsignedInteger('visualizaciones')->default(0);
            $table->unsignedInteger('consultas_recibidas')->default(0);
            $table->unsignedInteger('completaron_formulario')->default(0);
            $table->unsignedInteger('contactaron_whatsapp')->default(0);
            $table->unsignedInteger('vieron_datos')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['property_id', 'portal', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_portal_reports');
    }
};
