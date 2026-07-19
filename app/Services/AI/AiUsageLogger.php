<?php

namespace App\Services\AI;

use App\Models\AiUsageLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Registra el gasto real de cada llamada a IA (texto o imagen) para poder
 * volcarlo a Finanzas — ver App\Console\Commands\RollUpAiUsageDaily.
 * Nunca debe tumbar la llamada real si falla el registro.
 */
class AiUsageLogger
{
    public static function record(string $service, string $provider, string $model, int $inputTokens, int $outputTokens, ?Model $related = null): void
    {
        try {
            // ojo: NO usar config("ai_pricing.models.{$model}") — los IDs de
            // modelo con puntos (gemini-3.1-flash-image) rompen la notación
            // de puntos de Laravel y el lookup falla en silencio.
            $pricing = config('ai_pricing.models')[$model] ?? null;

            $cost = 0.0;
            if ($pricing) {
                $cost = ($inputTokens / 1_000_000) * $pricing['input']
                      + ($outputTokens / 1_000_000) * $pricing['output']
                      + ($pricing['request_fee'] ?? 0);
            }

            AiUsageLog::create([
                'service'       => $service,
                'provider'      => $provider,
                'model'         => $model,
                'input_tokens'  => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost_usd'      => round($cost, 4),
                'related_type'  => $related?->getMorphClass(),
                'related_id'    => $related?->getKey(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AiUsageLogger: no se pudo registrar uso', ['error' => $e->getMessage()]);
        }
    }
}
