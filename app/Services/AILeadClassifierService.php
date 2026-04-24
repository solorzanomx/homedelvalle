<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AILeadClassifierService
{
    private string $apiKey;
    private string $model = 'gemini-2.0-flash';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? '';
    }

    /**
     * Classify a contact form submission.
     *
     * Returns array:
     *   is_spam    bool
     *   category   string  vendedor|comprador|desarrollador|arrendador|arrendatario|otro
     *   urgency    string  alta|media|baja
     *   summary    string  One-line summary in Spanish
     *   spam_reason string|null
     */
    public function classify(array $data): array
    {
        $default = [
            'is_spam'     => false,
            'category'    => 'otro',
            'urgency'     => 'media',
            'summary'     => '',
            'spam_reason' => null,
        ];

        if (empty($this->apiKey)) {
            return $default;
        }

        $prompt = $this->buildPrompt($data);

        try {
            $response = Http::timeout(8)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [[
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0,
                        'maxOutputTokens'  => 200,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('AILeadClassifier: API error', ['status' => $response->status()]);
                return $default;
            }

            $text = $response->json('candidates.0.content.parts.0.text', '{}');
            $result = json_decode($text, true);

            if (! is_array($result)) {
                return $default;
            }

            return [
                'is_spam'     => (bool) ($result['is_spam'] ?? false),
                'category'    => $result['category'] ?? 'otro',
                'urgency'     => $result['urgency'] ?? 'media',
                'summary'     => $result['summary'] ?? '',
                'spam_reason' => $result['spam_reason'] ?? null,
            ];

        } catch (\Throwable $e) {
            Log::warning('AILeadClassifier: exception', ['error' => $e->getMessage()]);
            return $default;
        }
    }

    private function buildPrompt(array $data): string
    {
        $name    = $data['name'] ?? '';
        $email   = $data['email'] ?? '';
        $phone   = $data['phone'] ?? '';
        $message = $data['message'] ?? '';

        return <<<PROMPT
Eres un clasificador de leads para una inmobiliaria boutique en Ciudad de México (Colonia del Valle, Benito Juárez).
Analiza este formulario de contacto y responde SOLO con JSON válido, sin texto adicional.

Nombre: {$name}
Email: {$email}
Teléfono: {$phone}
Mensaje: {$message}

Reglas:
- is_spam = true si: ofrece servicios externos (SEO, diseño, créditos, seguros, limpieza, etc.), mensaje sin sentido, email de dominio sospechoso, o claramente no es un cliente inmobiliario.
- category: "vendedor" (quiere vender propiedad), "comprador" (quiere comprar), "desarrollador" (proyecto de desarrollo), "arrendador" (quiere rentar su propiedad), "arrendatario" (busca renta), "otro".
- urgency: "alta" (menciona fecha límite, urgencia, ya decidido), "media" (interés claro sin urgencia), "baja" (solo curiosidad o muy vago).
- summary: máximo 15 palabras en español describiendo el lead. Vacío si es spam.
- spam_reason: razón breve si is_spam = true, null si no es spam.

Responde únicamente con este JSON:
{"is_spam": bool, "category": "string", "urgency": "string", "summary": "string", "spam_reason": "string|null"}
PROMPT;
    }
}
