<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractClauseSuggestion;
use App\Models\Notification;
use App\Services\AI\AIManager;
use App\Services\AI\Concerns\ParsesJsonFromLlm;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ContractClauseSuggestionService
{
    use ParsesJsonFromLlm;

    public function __construct(
        protected AIManager $ai,
        protected ContractClauseVariableResolver $resolver,
    ) {}

    public function generate(Contract $contract): void
    {
        try {
            $contract->loadMissing(['clauses', 'operation', 'rentalProcess']);

            $system = $this->buildSystemPrompt();
            $prompt = $this->buildPrompt($contract);

            $raw = null;
            $parsed = null;
            $lastError = null;
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $raw = $this->ai->agent('contracts.clause_suggestions', $prompt, $system);
                try {
                    $parsed = $this->parseJsonBlock($raw);
                    break;
                } catch (RuntimeException $e) {
                    $lastError = $e;
                    Log::warning("ContractClauseSuggestionService: intento {$attempt} con JSON inválido para contract {$contract->id}");
                }
            }

            if ($parsed === null) {
                throw $lastError;
            }

            $suggestions = $parsed['suggestions'] ?? [];
            $validClauseIds = $contract->clauses->pluck('id')->all();

            foreach ($suggestions as $item) {
                $type = $item['type'] ?? null;
                if (!in_array($type, ['edit', 'add', 'remove'], true)) {
                    continue;
                }

                $clauseId = $item['clause_id'] ?? null;
                if ($clauseId && !in_array($clauseId, $validClauseIds, true)) {
                    $clauseId = null; // la IA referenció un id que no pertenece a este contrato
                }
                if ($type !== 'add' && !$clauseId) {
                    continue; // edit/remove sin cláusula válida no se puede aplicar
                }

                ContractClauseSuggestion::create([
                    'contract_id' => $contract->id,
                    'contract_clause_id' => $clauseId,
                    'suggestion_type' => $type,
                    'proposed_title' => $item['title'] ?? null,
                    'proposed_body' => $item['body'] ?? null,
                    'rationale' => $item['rationale'] ?? null,
                    'status' => 'pending',
                ]);
            }

            $contract->update(['ai_suggestion_status' => 'ready']);

            $this->notify($contract, count($suggestions));
        } catch (\Throwable $e) {
            Log::warning('ContractClauseSuggestionService: fallo generando sugerencias para contract ' . $contract->id . ': ' . $e->getMessage());
            $contract->update(['ai_suggestion_status' => 'failed']);
        }
    }

    protected function notify(Contract $contract, int $count): void
    {
        $alreadyNotifiedToday = Notification::where('type', 'contract_clause_suggestions_ready')
            ->where('data->contract_id', $contract->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->exists();

        if ($alreadyNotifiedToday) {
            return;
        }

        $userId = $contract->operation?->user_id ?? $contract->rentalProcess?->user_id;
        if (!$userId) {
            return;
        }

        $url = $contract->operation_id
            ? route('operations.show', $contract->operation_id)
            : route('rentals.show', $contract->rental_process_id);

        Notification::create([
            'user_id' => $userId,
            'type' => 'contract_clause_suggestions_ready',
            'title' => 'Sugerencias de cláusulas listas',
            'body' => "La IA propuso {$count} cambio(s) para \"{$contract->title}\" — revísalos.",
            'data' => ['url' => $url, 'contract_id' => $contract->id],
        ]);
    }

    protected function buildSystemPrompt(): string
    {
        return <<<SYSTEM
Eres un asistente de análisis contractual para un broker inmobiliario con licencia en la Ciudad de México, dentro de un CRM real. NO tienes autoridad legal ni sustituyes a un abogado o notario — tu trabajo es señalar posibles inconsistencias o ajustes puntuales entre los datos del trato y el texto ya redactado de las cláusulas, para que el broker decida.

Reglas estrictas:
- Solo sugiere cambios concretos y accionables, nunca reescribas el contrato completo.
- Si algo requiere revisión de un abogado o notario (montos de penalización, cuestiones fiscales complejas, temas no estándar), dilo explícitamente en el "rationale" en vez de inventar una cifra o cláusula legal nueva.
- No agregues cláusulas genéricas de relleno — solo si detectas una omisión real relevante al trato.
- Responde ÚNICAMENTE con JSON válido, sin texto ni explicación fuera del JSON, sin markdown.

Formato de respuesta (JSON):
{"suggestions": [{"type": "edit"|"add"|"remove", "clause_id": <id numérico o null si type=add>, "title": "...", "body": "<p>HTML del cuerpo de la cláusula...</p>", "rationale": "por qué se sugiere, en 1-2 frases"}]}

Si no hay ningún cambio que sugerir, responde: {"suggestions": []}
SYSTEM;
    }

    protected function buildPrompt(Contract $contract): string
    {
        $tokens = $contract->operation_id
            ? $this->resolver->resolveForOperation($contract->operation)
            : $this->resolver->resolveForRental($contract->rentalProcess);

        $context = collect($tokens)
            ->reject(fn ($v, $k) => in_array($k, ['{{fecha_actual}}', '{{empresa_nombre}}', '{{fecha_firma_texto}}']))
            ->map(fn ($v, $k) => trim($k, '{}') . ': ' . $v)
            ->implode("\n");

        $clausesList = $contract->clauses->map(function ($clause) {
            $body = strip_tags($clause->body);
            $body = mb_strlen($body) > 600 ? mb_substr($body, 0, 600) . '…' : $body;
            return "[ID {$clause->id}] ({$clause->section}) {$clause->title}: {$body}";
        })->implode("\n\n");

        return <<<PROMPT
Datos del trato:
{$context}

Cláusulas actuales del contrato (con su ID real en la base de datos):
{$clausesList}

Revisa si el texto de las cláusulas es consistente con los datos del trato de arriba (por ejemplo: forma de pago mencionada, si hay crédito hipotecario o aval, plazos, montos). Sugiere solo los cambios puntuales que encuentres necesarios.
PROMPT;
    }
}
