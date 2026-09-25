<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Services\AI\Providers\AnthropicProvider;
use App\Support\DocumentReviewInbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Lee un estado de cuenta bancario o un recibo de nómina (imagen o PDF) con
 * Claude y valida por CONTENIDO (2026-09-25): que de verdad sea ese tipo de
 * documento, que el titular sea el cliente y que el periodo sea reciente
 * (se piden los últimos 3 meses). Mismo patrón y mismos estados que
 * AddressDocumentAIExtractionService / IdDocumentAIVerificationService, para
 * que el CRM lo muestre igual (🤖 coincide / no coincide / vencida / no se pudo leer).
 *
 * Es una AYUDA para el asesor, no un veredicto. Nunca debe tronar una subida.
 */
class StatementDocumentAIExtractionService
{
    public const CATEGORIES = ['estado_cuenta', 'nomina'];

    public const MEDIA_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];

    /** Meses hacia atrás que se aceptan para el fin del periodo. */
    public const MAX_AGE_MONTHS = 3;

    public function shouldExtract(Document $document): bool
    {
        return in_array($document->category, self::CATEGORIES, true);
    }

    public function extract(Document $document, ?Client $client = null): void
    {
        try {
            if (! in_array($document->mime_type, self::MEDIA_TYPES, true)) {
                return; // doc/docx: sin lectura automática
            }

            $bytes = $this->readFile($document->file_path);
            if ($bytes === null) {
                $document->update(['ai_verification_status' => 'error', 'ai_verification_notes' => 'No se pudo leer el archivo subido.']);
                return;
            }

            $data = $this->callAi($bytes, $document->mime_type, $document->category);
            $client ??= $document->client;

            [$status, $notes] = $this->evaluate($data, $document->category, $client);

            $document->update([
                'ai_extracted_data' => $data,
                'ai_verification_status' => $status,
                'ai_verification_notes' => $notes,
            ]);
        } catch (\Throwable $e) {
            Log::warning('StatementDocumentAIExtractionService falló', ['document_id' => $document->id, 'error' => $e->getMessage()]);
            $document->update([
                'ai_verification_status' => 'error',
                'ai_verification_notes' => 'No se pudo leer automáticamente en este momento.',
            ]);
        }
    }

    /**
     * Reglas de negocio sobre lo leído. Separado de la llamada a la IA para poder probarlo sin red.
     *
     * @return array{0:string,1:string} [status, notas]
     */
    public function evaluate(?array $data, string $category, ?Client $client): array
    {
        $what = $category === 'nomina' ? 'recibo de nómina' : 'estado de cuenta';

        if (! $data || empty($data['legible'])) {
            return ['unreadable', "No se alcanzó a leer el {$what} automáticamente."];
        }
        if (array_key_exists('tipo_correcto', $data) && $data['tipo_correcto'] === false) {
            return ['mismatch', "No parece un {$what}: revísalo con cuidado."];
        }

        $notes = [];
        $status = 'match';

        $similar = $client ? DocumentReviewInbox::similar($client->name, $data['titular'] ?? null) : null;
        if ($similar === false) {
            $status = 'mismatch';
            $notes[] = "El titular (\"{$data['titular']}\") no coincide con el cliente (\"{$client->name}\").";
        } elseif ($similar === true) {
            $notes[] = 'Titular coincide.';
        }

        if (! empty($data['periodo_fin'])) {
            try {
                $fin = \Carbon\Carbon::parse($data['periodo_fin']);
                if ($fin->lt(now()->subMonths(self::MAX_AGE_MONTHS)->startOfMonth())) {
                    if ($status === 'match') {
                        $status = 'expired';
                    }
                    $notes[] = 'El periodo termina en ' . $fin->translatedFormat('F Y') . ' — es más antiguo que los últimos ' . self::MAX_AGE_MONTHS . ' meses.';
                } else {
                    $notes[] = 'Periodo hasta ' . $fin->translatedFormat('F Y') . ', reciente.';
                }
            } catch (\Throwable) {
                // fecha no parseable: se ignora sin tronar
            }
        }

        return [$status, implode(' ', $notes)];
    }

    private function readFile(string $path): ?string
    {
        if (str_starts_with($path, '/')) {
            return file_exists($path) ? file_get_contents($path) : null;
        }

        return Storage::disk('public')->exists($path) ? Storage::disk('public')->get($path) : null;
    }

    private function callAi(string $bytes, string $mimeType, string $category): ?array
    {
        $what = $category === 'nomina' ? 'un recibo de nómina' : 'un estado de cuenta bancario';
        $prompt = <<<PROMPT
Este archivo debería ser {$what} mexicano. Extrae estos datos y responde ÚNICAMENTE con un JSON válido, sin texto adicional:

{
  "legible": true|false,
  "tipo_correcto": true|false,
  "titular": "nombre completo del titular/empleado o null",
  "institucion": "banco (estado de cuenta) o empresa patrón (nómina), o null",
  "periodo_inicio": "YYYY-MM-DD o null",
  "periodo_fin": "YYYY-MM-DD o null (fin del periodo o corte; si solo hay mes/año, el último día del mes)",
  "ingreso_neto": "monto neto pagado (solo nómina) como texto, ej. '18,450.00', o null"
}

Reglas:
- "legible" es false si está borroso, incompleto o no se puede leer con confianza — los demás campos van null.
- "tipo_correcto" es false si el documento claramente NO es {$what} (por ejemplo, es otro tipo de recibo o una captura cualquiera).
- No inventes datos: si no aparece, null.
- No agregues explicación ni texto fuera del JSON.
PROMPT;

        $raw = (new AnthropicProvider())->completeVision(
            base64_encode($bytes),
            $mimeType === 'image/jpg' ? 'image/jpeg' : $mimeType,
            $prompt,
            null,
            ['_service' => 'portal.statement_extraction'],
        );

        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }
        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }
}
