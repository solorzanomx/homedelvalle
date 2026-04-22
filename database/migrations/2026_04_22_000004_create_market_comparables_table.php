<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_comparables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_colonia_id')->constrained()->cascadeOnDelete();
            $table->enum('property_type', ['apartment', 'house', 'land', 'office'])->default('apartment');

            // Ubicación anonimizada (sin número exacto)
            $table->string('address_hint')->nullable();
            $table->decimal('m2_total',        8, 2)->nullable();
            $table->decimal('m2_construction', 8, 2)->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('parking')->nullable();
            $table->unsignedSmallInteger('age_years')->nullable();
            $table->unsignedTinyInteger('floor')->nullable();

            $table->decimal('list_price', 14, 2)->nullable();
            $table->decimal('sale_price', 14, 2)->nullable(); // precio de cierre si se conoce
            $table->decimal('price_m2',   10, 2);            // calculado

            $table->date('transaction_date')->nullable();
            $table->enum('source', ['perplexity', 'portal', 'manual', 'own'])->default('manual');
            $table->string('source_url', 500)->nullable();
            $table->boolean('is_verified')->default(false);

            $table->timestamps();
            $table->index('market_colonia_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_comparables');
    }
};
