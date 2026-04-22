<?php

namespace App\Contracts;

interface AIProviderContract
{
    /**
     * Send a completion request and return the text response.
     *
     * @param  string  $prompt   Full prompt (system + user combined or just user)
     * @param  string|null  $system  System prompt (providers that support it separately)
     * @param  array   $options  Provider-specific overrides (temperature, max_tokens, etc.)
     */
    public function complete(string $prompt, ?string $system = null, array $options = []): string;

    /**
     * Whether this provider supports real-time web search grounding.
     */
    public function supportsWebSearch(): bool;

    /**
     * Provider identifier (matches config/ai.php keys).
     */
    public function name(): string;
}
