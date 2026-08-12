<?php

use App\Models\AiAgentConfig;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AiAgentConfig::firstOrCreate(
            ['key' => 'contracts.clause_suggestions'],
            [
                'label' => 'Sugerencias de cláusulas de contrato',
                'description' => 'Revisa el contexto de un trato contra sus cláusulas ya clonadas y sugiere ediciones/adiciones para que el broker apruebe o rechace.',
                'provider' => 'anthropic',
                'model' => 'claude-sonnet-4-6',
                'max_tokens' => 4096,
                'temperature' => 0.4,
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        AiAgentConfig::where('key', 'contracts.clause_suggestions')->delete();
    }
};
