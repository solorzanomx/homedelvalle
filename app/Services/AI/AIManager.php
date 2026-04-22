<?php

namespace App\Services\AI;

use App\Contracts\AIProviderContract;
use App\Models\AiAgentConfig;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\PerplexityProvider;
use InvalidArgumentException;

class AIManager
{
    /** @var array<string, AIProviderContract> */
    private array $resolved = [];

    /**
     * Get the default text-generation provider.
     */
    public function provider(?string $name = null): AIProviderContract
    {
        $name ??= config('ai.default_provider', 'anthropic');
        return $this->resolve($name);
    }

    /**
     * Get the web-search provider (Perplexity by default).
     */
    public function webSearch(): AIProviderContract
    {
        $name = config('ai.web_search_provider', 'perplexity');
        return $this->resolve($name);
    }

    /**
     * Shortcut: run a completion with the default provider.
     */
    public function complete(string $prompt, ?string $system = null, array $options = []): string
    {
        return $this->provider()->complete($prompt, $system, $options);
    }

    /**
     * Shortcut: run a completion with the web-search provider.
     */
    public function search(string $prompt, ?string $system = null, array $options = []): string
    {
        return $this->webSearch()->complete($prompt, $system, $options);
    }

    /**
     * Run a completion using the DB-configured agent.
     * Falls back to default provider if agent key not found.
     *
     * @param string $agentKey  e.g. 'market.analysis', 'valuation.narrative'
     * @param array  $override  Extra options that take precedence over DB config
     */
    public function agent(string $agentKey, string $prompt, ?string $system = null, array $override = []): string
    {
        $cfg      = AiAgentConfig::optionsFor($agentKey);
        $provider = $this->resolve($cfg['provider'] ?? config('ai.default_provider', 'anthropic'));
        $options  = array_merge($cfg, $override);
        unset($options['provider']); // provider key not needed in $options

        return $provider->complete($prompt, $system, $options);
    }

    private function resolve(string $name): AIProviderContract
    {
        if (!isset($this->resolved[$name])) {
            $this->resolved[$name] = match ($name) {
                'anthropic'  => new AnthropicProvider(),
                'openai'     => new OpenAIProvider(),
                'perplexity' => new PerplexityProvider(),
                default      => throw new InvalidArgumentException("AI provider desconocido: [{$name}]"),
            };
        }

        return $this->resolved[$name];
    }
}
