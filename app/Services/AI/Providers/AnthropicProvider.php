<?php

namespace App\Services\AI\Providers;

use App\Contracts\AIProviderContract;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicProvider implements AIProviderContract
{
    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private float  $temperature;

    public function __construct()
    {
        $this->apiKey      = config('services.anthropic.api_key') ?? '';
        $this->model       = config('ai.anthropic.model', 'claude-sonnet-4-6');
        $this->maxTokens   = config('ai.anthropic.max_tokens', 4096);
        $this->temperature = config('ai.anthropic.temperature', 0.7);
    }

    public function complete(string $prompt, ?string $system = null, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY no configurada.');
        }

        $payload = [
            'model'      => $options['model']       ?? $this->model,
            'max_tokens' => $options['max_tokens']   ?? $this->maxTokens,
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        if ($system) {
            $payload['system'] = $system;
        }

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        } else {
            $payload['temperature'] = $this->temperature;
        }

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2024-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Anthropic API error: ' . $response->body());
        }

        return $response->json('content.0.text') ?? '';
    }

    public function supportsWebSearch(): bool
    {
        return false;
    }

    public function name(): string
    {
        return 'anthropic';
    }
}
