<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_price_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_colonia_id')->constrained()->cascadeOnDelete();
            $table->enum('property_type', ['apartment', 'house', 'land', 'office'])->default('apartment');
            $table->enum('age_category', ['new', 'mid', 'old'])->default('mid');
            // primer día del mes: 2026-04-01
            $table->date('period');

            $table->decimal('price_m2_low',  10, 2);
            $table->decimal('price_m2_avg',  10, 2);
            $table->decimal('price_m2_high', 10, 2);

            $table->unsignedTinyInteger('sample_size')->default(0);
            $table->enum('confidence', ['low', 'medium', 'high'])->default('medium');
            $table->enum('source', ['perplexity', 'manual', 'own_data', 'mixed'])->default('manual');
            $table->longText('source_raw')->nullable(); // JSON respuesta Perplexity
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['market_colonia_id', 'property_type', 'age_category', 'period'], 'mps_colonia_type_age_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_price_snapshots');
    }
};
