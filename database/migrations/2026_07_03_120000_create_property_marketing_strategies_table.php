<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_marketing_strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained()->cascadeOnDelete();
            $table->json('target_audience')->nullable();
            $table->text('positioning_summary')->nullable();
            $table->json('recommended_channels')->nullable();
            $table->json('key_selling_points')->nullable();
            $table->json('raw_ai_response')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('ai_agent_configs')->insert([
            'key'         => 'marketing.strategy',
            'label'       => 'Estrategia de Promoción',
            'description' => 'Genera el público objetivo, posicionamiento y canales recomendados para promover un inmueble.',
            'provider'    => 'anthropic',
            'model'       => 'claude-sonnet-4-6',
            'max_tokens'  => 1400,
            'temperature' => 0.65,
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('ai_agent_configs')->where('key', 'marketing.strategy')->delete();
        Schema::dropIfExists('property_marketing_strategies');
    }
};
