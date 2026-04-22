<?php

namespace App\Services\AI\Providers;

use App\Contracts\AIProviderContract;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIProvider implements AIProviderContract
{
    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private float  $temperature;

    public function __construct()
    {
        $this->apiKey      = config('services.openai.api_key') ?? '';
        $this->model       = config('ai.openai.model', 'gpt-4o');
        $this->maxTokens   = config('ai.openai.max_tokens', 4096);
        $this->temperature = config('ai.openai.temperature', 0.7);
    }

    public function complete(string $prompt, ?string $system = null, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY no configurada.');
        }

        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model'       => $options['model']       ?? $this->model,
            'max_tokens'  => $options['max_tokens']   ?? $this->maxTokens,
            'temperature' => $options['temperature']  ?? $this->temperature,
            'messages'    => $messages,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function supportsWebSearch(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'openai';
    }
}
