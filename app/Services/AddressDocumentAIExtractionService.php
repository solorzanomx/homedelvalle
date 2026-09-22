<?php

namespace App\Services;

use App\Models\Document;
use App\Services\AI\Providers\AnthropicProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Lee un comprobante de domicilio (recibo de agua, luz o gas) subido al
 * Portal con Claude (visión) y extrae la dirección — para llenar el
 * formulario de Domicilio automáticamente, mismo espíritu que
 * IdDocumentAIVerificationService pero para comprobantes de domicilio en
 * vez de identificaciones.
 *
 * Solo lee imágenes (JPG/PNG) — un PDF se marca 'unreadable' sin tronar.
 * Corre síncrono justo después del upload (cPanel sin queue worker).
 */
class AddressDocumentAIExtractionService
{
    public const CATEGORIES = ['agua', 'luz', 'gas'];

    private const ESTADOS = [
        'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua',
        'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero',
        'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla',
        'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco',
        'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas', 'Extranjero',
    ];

    public function shouldExtract(Document $document): bool
    {
        return in_array($document->category, self::CATEGORIES, true);
    }

    public function extract(Document $document): void
    {
        try {
            if (! in_array($document->mime_type, ['image/jpeg', 'image/jpg', 'image/png'], true)) {
                $document->update([
                    'ai_verification_status' => 'unreadable',
                    'ai_verification_notes'  => 'Solo se puede leer automáticamente en formato JPG o PNG (este archivo es ' . ($document->mime_type ?: 'de otro tipo') . ').',
                ]);
                return;
            }

            $bytes = $this->readFile($document->file_path);
            if ($bytes === null) {
                $document->update([
                    'ai_verification_status' => 'error',
                    'ai_verification_notes'  => 'No se pudo leer el archivo subido.',
                ]);
                return;
            }

            $data = $this->callAi($bytes, $document->mime_type);

            if (! $data || empty($data['legible'])) {
                $document->update([
                    'ai_extracted_data'       => $data,
                    'ai_verification_status'  => 'unreadable',
                    'ai_verification_notes'   => 'La imagen no es lo bastante clara para leer la dirección automáticamente.',
                ]);
                return;
            }

            // Regla del negocio: comprobante no mayor a 3 meses.
            $status = 'match';
            $notes  = [];
            if (! empty($data['fecha_recibo'])) {
                try {
                    $fecha = \Carbon\Carbon::parse($data['fecha_recibo']);
                    if ($fecha->lt(now()->subMonths(3))) {
                        $status = 'expired';
                        $notes[] = 'El recibo es de ' . $fecha->translatedFormat('F Y') . ' — tiene más de 3 meses, pide uno más reciente.';
                    } else {
                        $notes[] = 'Recibo de ' . $fecha->translatedFormat('F Y') . ', dentro de los últimos 3 meses.';
                    }
                } catch (\Throwable) {
                    // fecha no parseable, se ignora sin tronar
                }
            }

            $document->update([
                'ai_extracted_data'      => $data,
                'ai_verification_status' => $status,
                'ai_verification_notes'  => implode(' ', $notes),
            ]);
        } catch (\Throwable $e) {
            Log::warning('AddressDocumentAIExtractionService falló', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
            $document->update([
                'ai_verification_status' => 'error',
                'ai_verification_notes'  => 'No se pudo leer automáticamente en este momento.',
            ]);
        }
    }

    private function readFile(string $path): ?string
    {
        if (str_starts_with($path, '/')) {
            return file_exists($path) ? file_get_contents($path) : null;
        }
        return Storage::disk('public')->exists($path) ? Storage::disk('public')->get($path) : null;
    }

    private function callAi(string $bytes, string $mimeType): ?array
    {
        $estadosList = implode(', ', self::ESTADOS);

        $prompt = <<<PROMPT
Esta imagen es un recibo/boleta de servicio (agua, luz/CFE, o gas) de México. Extrae la dirección del domicilio al que corresponde el servicio y responde ÚNICAMENTE con un JSON válido, sin texto adicional, con esta forma exacta:

{
  "legible": true|false,
  "calle_numero": "string o null (calle y número, ej. 'Av. Insurgentes Sur 1234, Int. 5')",
  "colonia": "string o null",
  "alcaldia_municipio": "string o null",
  "estado": "uno de esta lista exacta o null: {$estadosList}",
  "codigo_postal": "string de 5 dígitos o null",
  "fecha_recibo": "YYYY-MM-DD o null (fecha de emisión o del periodo facturado, el día 1 del mes si solo hay mes/año)"
}

Reglas:
- "legible" es false si la imagen está borrosa, incompleta, o no es un recibo de servicio — en ese caso los demás campos van null.
- "estado" debe ser EXACTAMENTE uno de los nombres de la lista dada, sin abreviar ni agregar "Estado de" salvo que así aparezca en la lista.
- No agregues explicación, comentarios ni texto fuera del JSON.
PROMPT;

        $provider = new AnthropicProvider();
        $mediaType = $mimeType === 'image/jpg' ? 'image/jpeg' : $mimeType;

        $raw = $provider->completeVision(base64_encode($bytes), $mediaType, $prompt, null, ['_service' => 'portal.address_extraction']);

        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}
