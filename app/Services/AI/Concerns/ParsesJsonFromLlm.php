<?php

namespace App\Services\AI\Concerns;

use Illuminate\Support\Facades\Log;
use RuntimeException;

trait ParsesJsonFromLlm
{
    /**
     * Extrae el bloque {...} de una respuesta de la IA (tolerando fences de
     * markdown alrededor) y lo decodifica como JSON.
     */
    private function parseJsonBlock(string $raw): array
    {
        $clean = preg_replace('/^```(?:json)?\s*/m', '', $raw);
        $clean = preg_replace('/\s*```$/m', '', $clean);
        $clean = trim($clean);
        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');
        if ($start !== false && $end !== false) {
            $clean = substr($clean, $start, $end - $start + 1);
        }
        $decoded = json_decode($clean, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning(static::class . ': JSON parse error', ['raw' => substr($raw, 0, 800)]);
            throw new RuntimeException('La IA devolvió JSON inválido: ' . json_last_error_msg());
        }
        return $decoded;
    }
}
