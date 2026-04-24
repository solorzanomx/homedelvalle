<?php

namespace App\Actions\FacebookPost;

use App\Models\FacebookPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateFacebookPostAction
{
    public function execute(FacebookPost $post, string $rawContent): ?string
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            throw new \RuntimeException('ANTHROPIC_API_KEY no configurada en .env');
        }

        $systemPrompt = <<<SYSTEM
Eres un experto en marketing inmobiliario digital para México, especialista en contenido para Facebook de Home del Valle, inmobiliaria premium en Benito Juárez, CDMX.
Responde EXCLUSIVAMENTE con un objeto JSON válido. Sin markdown, sin bloques de código, sin texto adicional.
SYSTEM;

        $userPrompt = <<<USER
Basándote en este contenido, genera copy para una imagen de post de Facebook:

---
{$rawContent}
---

Genera el siguiente JSON:
{
  "headline": "Título llamativo (máximo 8 palabras, directo, en español)",
  "subheadline": "Complementa el headline (máximo 12 palabras)",
  "body_text": "1-2 oraciones de apoyo para la imagen (máximo 30 palabras)",
  "caption": "Texto completo del post de Facebook (máximo 280 caracteres, tono conversacional, termina con CTA implícito, sin hashtags aquí)",
  "hashtags": ["InmueblesDF", "BienesRaicesMexico", "HomedelValle", "CDMX", "BieneRaices"],
  "bg_prompt": "Descripción en inglés para generar la imagen de fondo con IA (escena fotorrealista que complementa el tema, sin texto, sin personas salvo que sea esencial, iluminación natural, interior/exterior inmobiliario CDMX)"
}

Reglas:
- headline y subheadline van dentro de la IMAGEN (breves, impacto visual)
- caption es el texto del POST de Facebook (no aparece en la imagen)
- hashtags: array de 5-8 strings sin el símbolo #
- bg_prompt: en inglés, descriptivo, orientado a fotografía inmobiliaria premium CDMX
- Todo lo demás en español, tono premium pero accesible
USER;

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])
        ->timeout(60)
        ->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-opus-4-6',
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("Claude API error ({$response->status()}): {$error}");
        }

        $text = $response->json('content.0.text', '');
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/', '', $text);

        $data = json_decode(trim($text), true);

        if (!is_array($data)) {
            Log::error('GenerateFacebookPostAction: invalid JSON from Claude', ['raw' => substr($text, 0, 500)]);
            throw new \RuntimeException('Claude no devolvió JSON válido.');
        }

        $post->update([
            'headline'    => $data['headline'] ?? null,
            'subheadline' => $data['subheadline'] ?? null,
            'body_text'   => $data['body_text'] ?? null,
            'caption'     => $data['caption'] ?? null,
            'hashtags'    => $data['hashtags'] ?? [],
        ]);

        Log::info('GenerateFacebookPostAction: generated', ['post_id' => $post->id]);

        return $data['bg_prompt'] ?? null;
    }
}
