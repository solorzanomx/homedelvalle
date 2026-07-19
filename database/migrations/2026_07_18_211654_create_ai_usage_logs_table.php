<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de cada llamada a IA (texto o imagen) para poder contabilizar
 * el gasto real y volcarlo a Finanzas — ver AiUsageLogger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('service');   // 'blog.generation', 'blog.images', 'carousel.images', etc.
            $table->string('provider');  // 'anthropic', 'perplexity', 'gemini'
            $table->string('model');
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 4)->default(0);
            $table->nullableMorphs('related');
            $table->timestamps();

            $table->index(['created_at', 'service']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
