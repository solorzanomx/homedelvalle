<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_comparables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valuation_id')->constrained('property_valuations')->cascadeOnDelete();
            $table->foreignId('comparable_id')
                  ->nullable()
                  ->constrained('market_comparables')
                  ->nullOnDelete();

            // Datos inline si no está en market_comparables
            $table->string('description', 300)->nullable();
            $table->decimal('price_m2', 10, 2);
            $table->enum('distance_category', ['same_colonia', 'adjacent', 'same_zone'])->default('same_colonia');
            $table->text('relevance_note')->nullable();

            $table->timestamps();
            $table->index('valuation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_comparables');
    }
};
