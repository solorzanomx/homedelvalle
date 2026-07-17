<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AILeadClassifierService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? '';
        // Configurable: Google retira modelos sin aviso (gemini-2.0-flash
        // murió con 404 en 2026-07 y este servicio falló EN SILENCIO meses
        // por su propio fallback). Ante el próximo retiro: GEMINI_MODEL en .env.
        $this->model = config('services.gemini.model') ?? 'gemini-flash-lite-latest';
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

    /**
     * Clasifica un lead de portal (EasyBroker/Inmuebles24): quién es y qué
     * tan caliente viene, con el contexto de la propiedad por la que preguntó.
     *
     * Returns:
     *   ok          bool    la IA respondió (false → usar heurística)
     *   rol         string  comprador|inquilino|broker_colaboracion|spam|otro
     *   temperatura string  hot|warm|cold
     *   resumen     string  una línea en español
     */
    public function classifyPortalLead(array $data): array
    {
        $default = ['ok' => false, 'rol' => 'otro', 'temperatura' => 'warm', 'resumen' => ''];

        if (empty($this->apiKey)) {
            return $default;
        }

        $nombre    = $data['nombre'] ?? '';
        $email     = $data['email'] ?? '';
        $mensaje   = $data['mensaje'] ?? '';
        $portal    = $data['portal'] ?? 'EasyBroker';
        $propiedad = $data['propiedad'] ?? 'desconocida';

        $prompt = <<<PROMPT
Eres el clasificador de leads de Home del Valle, inmobiliaria boutique en Benito Juárez, CDMX.
Este contacto llegó desde un portal inmobiliario preguntando por una propiedad publicada.
Responde SOLO con JSON válido, sin texto adicional.

Portal de origen: {$portal}
Propiedad por la que preguntó: {$propiedad}
Nombre: {$nombre}
Email: {$email}
Mensaje: {$mensaje}

Reglas:
- rol:
  - "comprador": persona interesada en comprar la propiedad (o propiedades en venta).
  - "inquilino": persona interesada en rentar para vivir.
  - "broker_colaboracion": OTRO asesor/broker/inmobiliaria preguntando por colaboración, comisión compartida, o que dice tener un cliente. Pistas: "colega", "comparten comisión", "tengo cliente", "soy asesor", correo de otra inmobiliaria, tono profesional de intermediario.
  - "spam": ofrece servicios (marketing, créditos, seguros...), mensaje sin sentido o sin relación con la propiedad.
  - "otro": no se puede determinar.
  - Si la propiedad es de venta y el mensaje no dice lo contrario, asume "comprador"; si es de renta, "inquilino".
- temperatura:
  - "hot": quiere visitar/agendar, menciona fechas, forma de pago, urgencia, o preguntas muy específicas de la operación.
  - "warm": interés claro pero genérico ("¿sigue disponible?", "más información").
  - "cold": muy vago, solo curiosidad, o rol spam/broker_colaboracion.
- resumen: máximo 15 palabras en español, qué quiere este contacto. Ej: "Quiere visitar el depto de Narvarte esta semana, pagaría de contado".

Responde únicamente con este JSON:
{"rol": "string", "temperatura": "string", "resumen": "string"}
PROMPT;

        try {
            $response = Http::timeout(8)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [[
                        'parts' => [['text' => $prompt]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0,
                        'maxOutputTokens'  => 150,
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('AILeadClassifier: portal lead API error', ['status' => $response->status()]);
                return $default;
            }

            $result = json_decode($response->json('candidates.0.content.parts.0.text', '{}'), true);
            if (! is_array($result) || empty($result['rol'])) {
                return $default;
            }

            $rol  = in_array($result['rol'], ['comprador', 'inquilino', 'broker_colaboracion', 'spam', 'otro']) ? $result['rol'] : 'otro';
            $temp = in_array($result['temperatura'] ?? '', ['hot', 'warm', 'cold']) ? $result['temperatura'] : 'warm';

            return [
                'ok'          => true,
                'rol'         => $rol,
                'temperatura' => $temp,
                'resumen'     => mb_substr((string) ($result['resumen'] ?? ''), 0, 160),
            ];
        } catch (\Throwable $e) {
            Log::warning('AILeadClassifier: portal lead exception', ['error' => $e->getMessage()]);
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
