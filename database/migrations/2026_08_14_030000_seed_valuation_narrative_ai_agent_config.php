<?php

use App\Models\AiAgentConfig;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AiAgentConfig::firstOrCreate(
            ['key' => 'valuation.narrative'],
            [
                'label' => 'Análisis profesional de valuación',
                'description' => 'Genera el análisis narrativo (mercado, fortalezas, riesgo, recomendación comercial) que acompaña a cada Opinión de Valor.',
                'provider' => 'anthropic',
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 8192,
                'temperature' => 0.7,
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        AiAgentConfig::where('key', 'valuation.narrative')->delete();
    }
};
