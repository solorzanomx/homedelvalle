<?php

/**
 * Tarifas por modelo ($/1M tokens) para contabilizar el gasto real de IA —
 * ver App\Services\AI\AiUsageLogger. Verificado contra las APIs reales el
 * 2026-07-18 (Anthropic, Gemini) — Perplexity incluye además una tarifa fija
 * por request (aprox., tarifa "medium" — la respuesta no indica qué tamaño
 * de contexto de búsqueda se usó realmente).
 */
return [

    'usd_mxn_rate' => (float) env('USD_MXN_RATE', 18.5),

    'models' => [
        // Anthropic — texto (BlogAIService, valuaciones, marketing, clasificador de leads)
        'claude-sonnet-4-6'         => ['input' => 3.00, 'output' => 15.00],
        'claude-opus-4-6'           => ['input' => 5.00, 'output' => 25.00],
        'claude-haiku-4-5-20251001' => ['input' => 1.00, 'output' => 5.00],
        'claude-haiku-4-5'          => ['input' => 1.00, 'output' => 5.00],

        // Perplexity — búsqueda de mercado
        'sonar'     => ['input' => 1.00, 'output' => 1.00, 'request_fee' => 0.008],
        'sonar-pro' => ['input' => 3.00, 'output' => 15.00, 'request_fee' => 0.010],

        // Gemini — imágenes de carrusel (blog ya migró a OpenAI, ver abajo)
        'gemini-3.1-flash-image'         => ['input' => 0.50, 'output' => 60.00],
        'gemini-3.1-flash-image-preview' => ['input' => 0.50, 'output' => 60.00],

        // OpenAI — imágenes de blog (migrado 2026-07-18, ~9x más barato que Gemini)
        'gpt-image-1-mini' => ['input' => 2.00, 'output' => 8.00],
    ],

];
