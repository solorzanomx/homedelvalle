<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('valuation_id')->constrained('property_valuations')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->string('factor_key', 60);    // 'age_depreciation', 'condition_premium'...
            $table->string('factor_label', 120); // "Depreciación por antigüedad (30 años)"

            $table->enum('adjustment_type', ['percent', 'absolute'])->default('percent');
            $table->decimal('adjustment_value', 7, 4); // -0.2200 = -22%

            $table->decimal('price_before', 10, 2);
            $table->decimal('price_after',  10, 2);

            $table->text('explanation')->nullable();
            $table->timestamps();

            $table->index('valuation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_adjustments');
    }
};
