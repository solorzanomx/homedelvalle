<?php

namespace App\Services\AI\Providers;

use App\Contracts\AIProviderContract;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PerplexityProvider implements AIProviderContract
{
    private string $apiKey;
    private string $model;
    private int    $maxTokens;

    public function __construct()
    {
        $this->apiKey    = config('services.perplexity.api_key') ?? '';
        $this->model     = config('ai.perplexity.model', 'sonar');
        $this->maxTokens = config('ai.perplexity.max_tokens', 2048);
    }

    public function complete(string $prompt, ?string $system = null, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('PERPLEXITY_API_KEY no configurada.');
        }

        $messages = [];
        if ($system) {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model'      => $options['model']      ?? $this->model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'messages'   => $messages,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.perplexity.ai/chat/completions', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Perplexity API error: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function supportsWebSearch(): bool
    {
        return true;
    }

    public function name(): string
    {
        return 'perplexity';
    }
}
