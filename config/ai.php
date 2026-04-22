<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI provider for text generation
    |--------------------------------------------------------------------------
    | Supported: "anthropic", "openai"
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'anthropic'),

    /*
    |--------------------------------------------------------------------------
    | Provider for web-search enriched queries
    |--------------------------------------------------------------------------
    | Supported: "perplexity"
    */
    'web_search_provider' => env('AI_WEB_SEARCH_PROVIDER', 'perplexity'),

    /*
    |--------------------------------------------------------------------------
    | Anthropic (Claude)
    |--------------------------------------------------------------------------
    */
    'anthropic' => [
        'model'       => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
        'max_tokens'  => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
        'temperature' => (float) env('ANTHROPIC_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (GPT-4o — optional future provider)
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'model'       => env('OPENAI_MODEL', 'gpt-4o'),
        'max_tokens'  => (int) env('OPENAI_MAX_TOKENS', 4096),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Perplexity (web-search grounded responses)
    |--------------------------------------------------------------------------
    */
    'perplexity' => [
        'model'      => env('PERPLEXITY_MODEL', 'sonar'),
        'max_tokens' => (int) env('PERPLEXITY_MAX_TOKENS', 2048),
    ],

];
