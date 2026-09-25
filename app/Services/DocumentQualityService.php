<?php

namespace App\Services;

use App\Models\Document;
use App\Services\AI\Providers\AnthropicProvider;
use App\Support\DocumentUploadGuide;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Revisa la calidad de un documento ANTES de guardarlo (2026-09-25), para que
 * el cliente lo corrija en el momento y el asesor no reciba fotos ilegibles
 * (caso real: foto a la pantalla del teléfono con el estado de cuenta).
 *
 * Dos capas:
 *  1. Reglas locales, sin costo: tamaño mínimo, resolución mínima, PDF válido
 *     y sin contraseña.
 *  2. Revisión con IA (visión / PDF): detecta foto de pantalla, ilegible,
 *     recortado, borroso, oscuro. Solo BLOQUEA lo evidente (foto de pantalla,
 *     ilegible); lo dudoso pasa con una marca "Calidad dudosa" para el asesor.
 *
 * Nunca debe tronar la subida: si la IA falla o no está configurada se deja
 * pasar (fail-open). Y para no atorar al cliente por un falso positivo, el
 * tercer intento en la misma categoría se acepta con marca (ver MAX_BLOCKS).
 */
class DocumentQualityService
{
    const IMAGE_MIMES = ['image/jpeg', 'image/jpg', 'image/png'];

    /** Bloqueos previos permitidos por cliente+categoría antes de dejar pasar con marca. */
    const MAX_BLOCKS = 2;

    /** Modelo rápido y barato: es una revisión visual, no una extracción. */
    const MODEL = 'claude-haiku-4-5-20251001';

    /**
     * @return array{block:?string, warnings:string[], checked:bool}
     */
    public function gate(UploadedFile $file, ?string $category, ?int $clientId = null): array
    {
        $result = ['block' => null, 'warnings' => [], 'checked' => false];
        $kind = DocumentUploadGuide::kind($category);
        $mime = $file->getMimeType();
        $path = $file->getRealPath();

        if (! $path || ! is_file($path)) {
            return $result;
        }

        $bytes = file_get_contents($path);

        if (in_array($mime, self::IMAGE_MIMES, true)) {
            $local = $this->checkImageLocally($path, $kind, $file->getSize());
            if ($local) {
                return $this->block($result, $local, $category, $clientId, false);
            }
        } elseif ($mime === 'application/pdf') {
            $local = $this->checkPdfLocally($bytes);
            if ($local) {
                return $this->block($result, $local, $category, $clientId, false);
            }
        } else {
            return $result; // doc/docx u otros: sin revisión visual
        }

        // Los PDF de estados de cuenta, recibos y demás nacen digitales:
        // solo se revisan con IA los que pueden ser escaneos ilegibles, y no
        // los comprobantes de pago (capturas de pantalla legítimas).
        if ($mime === 'application/pdf' && $kind === DocumentUploadGuide::KIND_PAYMENT) {
            return $result;
        }

        $ai = $this->assessWithAI($bytes, $mime === 'image/jpg' ? 'image/jpeg' : $mime, $kind);
        if ($ai === null) {
            return $result; // fail-open
        }
        $result['checked'] = true;

        $isPdf = $mime === 'application/pdf';
        $why = $isPdf ? 'El archivo' : 'La foto';

        // Bloqueos: solo lo evidente.
        if (! empty($ai['foto_de_pantalla']) && $kind !== DocumentUploadGuide::KIND_PAYMENT && ! $isPdf) {
            return $this->block($result,
                'Parece una foto tomada a una pantalla (se nota el marco del teléfono o de la computadora), y así no se alcanza a leer bien. '
                . DocumentUploadGuide::betterWay($category), $category, $clientId);
        }
        if (array_key_exists('legible', $ai) && $ai['legible'] === false) {
            return $this->block($result,
                "{$why} no se alcanza a leer con claridad (" . ($ai['problema'] ?? 'borroso, oscuro o incompleto') . '). '
                . DocumentUploadGuide::betterWay($category), $category, $clientId);
        }

        // Avisos para el asesor (pasan, pero con marca).
        foreach ([
            'recortado' => 'parece estar cortado o incompleto',
            'borroso' => 'está algo borroso',
            'muy_oscuro' => 'está muy oscuro o con sombras',
            'reflejo_fuerte' => 'tiene reflejos fuertes que tapan datos',
            'documento_pequeno_en_foto' => 'el documento se ve muy pequeño dentro de la imagen',
        ] as $flag => $text) {
            if (! empty($ai[$flag])) {
                $result['warnings'][] = ucfirst($text) . '.';
            }
        }
        return $result;
    }

    /** Guarda en el documento el resultado de la revisión (marca "Calidad dudosa"). */
    public function record(Document $document, array $gate): void
    {
        \App\Models\DocumentEvent::log($document, 'uploaded', null, $document->uploaded_by);
        if (! empty($gate['warnings'])) {
            \App\Models\DocumentEvent::log($document, 'quality_warn', implode(' ', $gate['warnings']), $document->uploaded_by);
        }

        if (empty($gate['warnings'])) {
            if (! empty($gate['checked'])) {
                $document->update(['quality_status' => 'ok', 'quality_notes' => null]);
            }
            return;
        }
        $document->update([
            'quality_status' => 'warn',
            'quality_notes' => implode(' ', $gate['warnings']),
        ]);
    }

    /** $bypassable=false para fallas duras (archivo que no abre, PDF con contraseña, imagen diminuta). */
    private function block(array $result, string $message, ?string $category, ?int $clientId, bool $bypassable = true): array
    {
        if (! $bypassable) {
            $this->logBlock($clientId, $category, $message, false);
            $result['block'] = $message;
            return $result;
        }

        // Salida para falsos positivos: tras MAX_BLOCKS rechazos seguidos en la
        // misma categoría, el siguiente intento se acepta con marca.
        if ($clientId) {
            $key = "docq:blocks:{$clientId}:{$category}";
            $blocks = (int) Cache::get($key, 0);
            if ($blocks >= self::MAX_BLOCKS) {
                Cache::forget($key);
                $this->logBlock($clientId, $category, $message, true);
                $result['warnings'][] = 'El cliente lo subió después de ' . self::MAX_BLOCKS . ' intentos rechazados por calidad: ' . $message;
                return $result;
            }
            Cache::put($key, $blocks + 1, now()->addDay());
        }

        $this->logBlock($clientId, $category, $message, false);
        $result['block'] = $message;
        return $result;
    }

    /** Bitácora de bloqueos para afinar umbrales con datos reales (página de métricas). */
    private function logBlock(?int $clientId, ?string $category, string $message, bool $bypassed): void
    {
        try {
            \DB::table('document_quality_blocks')->insert([
                'client_id' => $clientId,
                'category' => $category,
                'reason' => mb_substr($message, 0, 300),
                'bypassed' => $bypassed,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::info('DocumentQualityService: bitácora de bloqueo omitida', ['error' => $e->getMessage()]);
        }
    }

    /** @return array{0:string,1:string} [bytes, mediaType] */
    private function downscale(string $bytes, string $mediaType): array
    {
        if (! function_exists('imagecreatefromstring')) {
            return [$bytes, $mediaType];
        }
        $img = @imagecreatefromstring($bytes);
        if (! $img) {
            return [$bytes, $mediaType];
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $long = max($w, $h);
        if ($long > 1600) {
            $scale = 1600 / $long;
            $resized = imagescale($img, (int) round($w * $scale), (int) round($h * $scale));
            if ($resized) {
                imagedestroy($img);
                $img = $resized;
            }
        }
        ob_start();
        imagejpeg($img, null, 80);
        $out = ob_get_clean();
        imagedestroy($img);

        return $out ? [$out, 'image/jpeg'] : [$bytes, $mediaType];
    }

    private function checkImageLocally(string $path, string $kind, ?int $size): ?string
    {
        if ($size !== null && $size < 15 * 1024) {
            return 'La imagen pesa muy poco y seguramente se ve muy pequeña o comprimida. Súbela de nuevo en su tamaño original.';
        }

        $info = @getimagesize($path);
        if (! $info) {
            return 'No pudimos abrir la imagen. Intenta subirla de nuevo o en PDF.';
        }

        [$w, $h] = $info;
        // Comprobantes de pago suelen ser capturas de pantalla angostas: umbral más bajo.
        [$minShort, $minLong] = $kind === DocumentUploadGuide::KIND_PAYMENT ? [300, 500] : [600, 900];
        if (min($w, $h) < $minShort || max($w, $h) < $minLong) {
            return "La imagen es muy pequeña ({$w}×{$h} px) para poder leerla. Tómala de nuevo con la cámara del teléfono en su tamaño original, o súbela en PDF.";
        }

        return null;
    }

    private function checkPdfLocally(string $bytes): ?string
    {
        if (! str_starts_with(ltrim(substr($bytes, 0, 1024)), '%PDF')) {
            return 'El archivo no parece ser un PDF válido. Descárgalo de nuevo e inténtalo otra vez.';
        }
        if (str_contains($bytes, '/Encrypt')) {
            return 'El PDF está protegido con contraseña y no lo podemos abrir. Descárgalo sin contraseña o quítale la protección antes de subirlo.';
        }
        return null;
    }

    private function assessWithAI(string $bytes, string $mediaType, string $kind): ?array
    {
        if ($mediaType !== 'application/pdf') {
            // Las fotos de celular pesan varios MB: se reducen antes de mandarlas
            // (más rápido, más barato y dentro del límite de la API). 1600 px
            // sobran para juzgar nitidez, encuadre y reflejos.
            [$bytes, $mediaType] = $this->downscale($bytes, $mediaType);
            if (strlen($bytes) > 3_500_000) {
                return null;
            }
        }

        $what = $mediaType === 'application/pdf' ? 'Este archivo es un PDF' : 'Esta imagen es una foto o captura';
        $prompt = <<<PROMPT
{$what} que un cliente subió como documento (tipo: {$kind}) a una inmobiliaria en México. Evalúa SOLO la calidad de captura para poder revisarlo, no su contenido. Responde ÚNICAMENTE con un JSON válido, sin texto adicional:

{
  "legible": true|false,
  "problema": "si legible es false, en pocas palabras por qué (ej. muy borroso, muy oscuro, casi todo cortado, casi en blanco), si no null",
  "foto_de_pantalla": true|false,
  "recortado": true|false,
  "borroso": true|false,
  "muy_oscuro": true|false,
  "reflejo_fuerte": true|false,
  "documento_pequeno_en_foto": true|false
}

Reglas:
- "foto_de_pantalla" es true SOLO si es una FOTOGRAFÍA tomada a una pantalla (se ve el marco del teléfono/monitor, franjas moiré, reflejos de pantalla, un dedo o mano sosteniendo el teléfono). Una captura de pantalla directa (sin marco) es false. Para PDF siempre false.
- "legible" es false solo si un asesor NO podría leer los datos principales (texto ilegible, casi en blanco, enorme parte cortada). Si se puede leer aunque con cierta dificultad, es true.
- "recortado": faltan bordes o partes del documento.
- "documento_pequeno_en_foto": el documento ocupa una parte pequeña de la imagen, con mucho fondo alrededor.
- Sé estricto con lo evidente y tolerante con lo dudoso.
PROMPT;

        try {
            $raw = (new AnthropicProvider())->completeVision(
                base64_encode($bytes), $mediaType, $prompt, null,
                ['_service' => 'portal.document_quality', 'model' => self::MODEL, 'max_tokens' => 300],
            );
            if (preg_match('/\{.*\}/s', $raw, $m)) {
                $raw = $m[0];
            }
            $data = json_decode($raw, true);
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            Log::info('DocumentQualityService: revisión IA omitida', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
