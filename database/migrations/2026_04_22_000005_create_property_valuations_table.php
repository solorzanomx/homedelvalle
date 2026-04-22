<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_valuations', function (Blueprint $table) {
            $table->id();
            // Nullable: se puede hacer una valuación standalone (sin inmueble registrado)
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // ── Datos de entrada capturados al momento de la valuación ──
            $table->foreignId('input_colonia_id')
                  ->nullable()
                  ->constrained('market_colonias')
                  ->nullOnDelete();
            $table->string('input_colonia_raw', 150)->nullable(); // fallback texto libre
            $table->enum('input_type', ['apartment', 'house', 'land', 'office'])->default('apartment');

            $table->decimal('input_m2_total', 8, 2);
            $table->decimal('input_m2_const', 8, 2)->nullable();
            $table->unsignedSmallInteger('input_age_years');
            $table->enum('input_condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->unsignedTinyInteger('input_bedrooms')->default(2);
            $table->unsignedTinyInteger('input_bathrooms')->default(1);
            $table->unsignedTinyInteger('input_parking')->default(0);
            $table->unsignedTinyInteger('input_floor')->nullable();
            $table->boolean('input_has_elevator')->default(false);
            $table->boolean('input_has_rooftop')->default(false);
            $table->boolean('input_has_balcony')->default(false);
            $table->boolean('input_has_service_room')->default(false);
            $table->boolean('input_has_storage')->default(false);
            $table->text('input_notes')->nullable();

            // ── Resultado del cálculo ──
            $table->decimal('base_price_m2',     10, 2)->nullable();
            $table->decimal('adjusted_price_m2', 10, 2)->nullable();
            $table->unsignedBigInteger('total_value_low')->nullable();
            $table->unsignedBigInteger('total_value_mid')->nullable();
            $table->unsignedBigInteger('total_value_high')->nullable();
            $table->unsignedBigInteger('suggested_list_price')->nullable();

            $table->enum('market_trend', ['rising', 'stable', 'falling'])->nullable();
            $table->enum('diagnosis', ['on_market', 'above_market', 'opportunity', 'insufficient_data'])->nullable();
            $table->enum('confidence', ['low', 'medium', 'high'])->nullable();

            // ── Fuente de datos usada ──
            $table->foreignId('snapshot_id')
                  ->nullable()
                  ->constrained('market_price_snapshots')
                  ->nullOnDelete();
            $table->boolean('used_perplexity')->default(false);
            $table->text('perplexity_query')->nullable();
            $table->longText('perplexity_response')->nullable();

            // ── Estado del documento ──
            $table->enum('status', ['draft', 'final', 'delivered'])->default('draft');
            $table->timestamp('delivered_at')->nullable();
            $table->string('pdf_path', 500)->nullable();

            $table->timestamps();

            $table->index('property_id');
            $table->index('created_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_valuations');
    }
};
