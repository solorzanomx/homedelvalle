<?php

namespace App\Services;

use App\Services\AI\AIManager;
use Illuminate\Support\Facades\Log;

/**
 * Clasificador de leads (formulario de contacto + portales).
 *
 * Historia (2026-07-17): usaba Gemini con key propia (GEMINI_API_KEY) que
 * nunca existió en producción, y encima Google retiró el modelo — el
 * fallback silencioso ocultó AMBAS fallas durante meses. Migrado al sistema
 * de Agentes IA del CRM (agente 'leads.classification', Admin → Agentes IA):
 * mismas keys de Anthropic que ya operan el Observatorio, y modelo editable
 * desde el panel sin tocar código.
 */
class AILeadClassifierService
{
    private const AGENT_KEY = 'leads.classification';

    public function __construct(private readonly AIManager $ai)
    {
    }

    /** Corre el agente y decodifica su respuesta JSON (tolerando fences ```). */
    private function runJson(string $prompt): ?array
    {
        $raw = $this->ai->agent(self::AGENT_KEY, $prompt);
        $raw = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($raw)));

        $result = json_decode($raw, true);

        return is_array($result) ? $result : null;
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

        $prompt = $this->buildPrompt($data);

        try {
            $result = $this->runJson($prompt);

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
- respuesta: el primer mensaje de WhatsApp listo para enviar a este contacto.
{$this->reglasDeRespuesta()}
  - Si rol es "spam", respuesta vacía "". Si es "broker_colaboracion", responde cordial preguntando qué busca su cliente (zona, presupuesto) y menciona que colaboramos con esquema de comisión compartida.

Responde únicamente con este JSON:
{"rol": "string", "temperatura": "string", "resumen": "string", "respuesta": "string"}
PROMPT;

        try {
            $result = $this->runJson($prompt);
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
                'respuesta'   => trim((string) ($result['respuesta'] ?? '')),
            ];
        } catch (\Throwable $e) {
            Log::warning('AILeadClassifier: portal lead exception', ['error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Reglas de redacción compartidas (tono de la ficha de marca —
     * docs/posicionamiento-marca.md — y política de seguimiento del manual).
     */
    private function reglasDeRespuesta(): string
    {
        return <<<'REGLAS'
  - Tono Home del Valle: técnico pero cercano, boutique — nunca suena a portal masivo ni a vendedor insistente.
  - Habla SIEMPRE en primera persona singular. Si se te da el nombre del asesor, preséntate: "soy {nombre} de Home del Valle". Si no se te da, di "te escribo de Home del Valle" — NUNCA "somos de Home del Valle" ni nombres inventados.
  - Si hay OBSERVACIONES INTERNAS del asesor, úsalas para afinar la respuesta (son contexto privado, no las cites textual): si señalan que el requerimiento es difícil o ambiguo, pide con honestidad la precisión que falta para buscar bien, sin prometer de más.
  - 2 a 4 líneas de WhatsApp, español de México, saluda por su nombre de pila, sin emojis o máximo uno.
  - Menciona la propiedad o lo que busca CON los datos reales dados (operación, precio, zona). NUNCA inventes datos, precios ni disponibilidad que no te dieron.
  - Incluye UNA pregunta calificadora natural (compra: forma de pago o tiempos; renta: fecha de mudanza o garantía; vendedor: motivo o tiempos).
  - Si el contacto busca opciones (brief de compra o renta), promete el proceso real: "te comparto 3-5 opciones curadas en máximo 72 horas".
  - A propietarios que quieren vender: ofrece la "opinión de valor gratuita" (JAMÁS digas "valuación gratuita").
  - Cierra invitando a la acción concreta (visita, llamada breve, o confirmar un dato).
  - Sin placeholders tipo [nombre] ni corchetes: texto final listo para enviar.
REGLAS;
    }

    /**
     * Redacta la primera respuesta de WhatsApp para CUALQUIER lead del CRM
     * (formularios del sitio o portales) usando todo su contexto. Devuelve
     * null si la IA no responde.
     */
    public function suggestReply(\App\Models\FormSubmission $lead, ?string $asesor = null): ?string
    {
        $payload = $lead->payload ?? [];

        // Contexto legible del brief (se omiten claves internas)
        $brief = collect($payload)
            ->except(['eb_request_id', 'eb_contact_id', 'ai_rol', 'ai_resumen', 'ai_respuesta', 'posible_broker', 'propiedad_local_id', 'eb_url', 'fecha_en_easybroker'])
            ->filter(fn ($v) => is_scalar($v) && $v !== '' && $v !== null)
            ->map(fn ($v, $k) => str_replace('_', ' ', $k) . ': ' . (is_array($v) ? implode(', ', $v) : $v))
            ->implode("\n");

        $tipos = [
            'vendedor'          => 'propietario que quiere vender su propiedad (pidió opinión de valor)',
            'vendedor_predio'   => 'propietario de predio interesado en vender a desarrolladora',
            'comprador'         => 'persona buscando comprar (dejó su brief de búsqueda)',
            'arrendatario'      => 'persona buscando rentar para vivir (dejó su brief)',
            'propietario_renta' => 'propietario que quiere poner su inmueble en renta',
            'easybroker'        => 'contacto de portal inmobiliario preguntando por una propiedad publicada',
            'contacto'          => 'consulta general del sitio',
            'b2b'               => 'desarrollador/inversionista con brief de inversión',
        ];
        $tipo = $tipos[$lead->form_type] ?? 'contacto del sitio';

        $lineaAsesor = $asesor ? "Asesor que enviará el mensaje: {$asesor}" : 'Asesor que enviará el mensaje: (no especificado)';
        $lineaNotas  = trim((string) $lead->notes) !== ''
            ? "OBSERVACIONES INTERNAS del asesor sobre este lead:\n{$lead->notes}"
            : 'OBSERVACIONES INTERNAS del asesor: (ninguna)';

        $prompt = <<<PROMPT
Eres el asistente comercial de Home del Valle, inmobiliaria boutique de Benito Juárez, CDMX.
Redacta el PRIMER mensaje de WhatsApp para este lead. Responde SOLO con JSON válido.

Tipo de lead: {$tipo}
Nombre: {$lead->full_name}
{$lineaAsesor}
Datos que dejó:
{$brief}

{$lineaNotas}

Reglas:
{$this->reglasDeRespuesta()}

Responde únicamente con este JSON:
{"respuesta": "string"}
PROMPT;

        try {
            $result = $this->runJson($prompt);
            $texto  = trim((string) ($result['respuesta'] ?? ''));

            return $texto !== '' ? $texto : null;
        } catch (\Throwable $e) {
            Log::warning('AILeadClassifier: suggestReply exception', ['error' => $e->getMessage()]);

            return null;
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
