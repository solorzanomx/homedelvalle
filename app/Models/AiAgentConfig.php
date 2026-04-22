<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AiAgentConfig extends Model
{
    protected $fillable = [
        'key', 'label', 'description',
        'provider', 'model', 'max_tokens', 'temperature', 'is_active',
    ];

    protected $casts = [
        'max_tokens'  => 'integer',
        'temperature' => 'float',
        'is_active'   => 'boolean',
    ];

    /** Available models per provider — used in admin UI dropdowns */
    public static array $providerModels = [
        'anthropic'  => [
            'claude-opus-4-6'           => 'Claude Opus 4.6 (más potente)',
            'claude-sonnet-4-6'         => 'Claude Sonnet 4.6 (equilibrado)',
            'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (más económico)',
        ],
        'perplexity' => [
            'sonar'            => 'Sonar (rápido, económico)',
            'sonar-pro'        => 'Sonar Pro (más preciso)',
            'sonar-reasoning'  => 'Sonar Reasoning (razonamiento)',
        ],
        'openai' => [
            'gpt-4o'      => 'GPT-4o (potente)',
            'gpt-4o-mini' => 'GPT-4o Mini (económico)',
            'dall-e-3'    => 'DALL-E 3 — $0.04/img standard · $0.08 HD',
            'dall-e-2'    => 'DALL-E 2 — $0.02/img (menor calidad)',
        ],
    ];

    /** Return cached options array for a given agent key. Falls back to config defaults. */
    public static function optionsFor(string $key): array
    {
        $config = Cache::remember("ai_agent:{$key}", 300, function () use ($key) {
            return static::where('key', $key)->where('is_active', true)->first();
        });

        if (!$config) {
            return [];
        }

        return [
            'provider'    => $config->provider,
            'model'       => $config->model,
            'max_tokens'  => $config->max_tokens,
            'temperature' => $config->temperature,
        ];
    }

    /** Flush cache for this agent after saving */
    protected static function booted(): void
    {
        static::saved(function (self $config) {
            Cache::forget("ai_agent:{$config->key}");
        });
    }
}
