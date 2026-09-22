<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Services\AI\Providers\AnthropicProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Lee una identificación oficial (INE, pasaporte) subida al Portal con
 * Claude (visión) y la compara contra los datos que el cliente capturó
 * (nombre, CURP, vigencia) — para que el asesor vea de una vez si algo no
 * coincide, en vez de tener que comparar a mano cada documento.
 *
 * Solo lee imágenes (JPG/PNG) — un PDF se marca 'unreadable' sin tronar,
 * no hay soporte de lectura de PDF aquí.
 *
 * Corre síncrono justo después del upload (mismo patrón que el resto del
 * proyecto — cPanel sin queue worker, ver CONTEXTO_PROYECTO.md). Nunca debe
 * tronar la subida del documento: cualquier falla se guarda como
 * ai_verification_status = 'error' y se sigue de largo.
 */
class IdDocumentAIVerificationService
{
    /** Categorías de identificación que vale la pena verificar */
    public const ID_CATEGORIES = ['ine_frente', 'ine_reverso', 'pasaporte', 'aval_ine_frente', 'aval_ine_reverso'];

    public function shouldVerify(Document $document): bool
    {
        return in_array($document->category, self::ID_CATEGORIES, true);
    }

    public function verify(Document $document, Client $client): void
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

            $extracted = $this->extract($bytes, $document->mime_type);

            if (! $extracted || empty($extracted['legible'])) {
                $document->update([
                    'ai_extracted_data'       => $extracted,
                    'ai_verification_status'  => 'unreadable',
                    'ai_verification_notes'   => 'La imagen no es lo bastante clara para leer los datos automáticamente.',
                ]);
                return;
            }

            // Foto tipo "hoja completa con la credencial chiquita en medio" —
            // el modelo puede leer los datos igual, pero con menos
            // confianza que una foto encuadrada. Se marca para que el
            // asesor lo sepa, en vez de dar por buena una lectura al
            // límite de lo legible.
            if (array_key_exists('documento_ocupa_mayoria_imagen', $extracted) && $extracted['documento_ocupa_mayoria_imagen'] === false) {
                $document->update([
                    'ai_extracted_data'      => $extracted,
                    'ai_verification_status' => 'unreadable',
                    'ai_verification_notes'  => 'La identificación se ve muy pequeña dentro de la foto (parece tener mucho espacio alrededor). Pide una foto donde la identificación llene el encuadre — con el botón "Usar cámara" del Portal queda bien de una vez.',
                ]);
                return;
            }

            [$status, $notes] = $this->compare($extracted, $client);

            $document->update([
                'ai_extracted_data'      => $extracted,
                'ai_verification_status' => $status,
                'ai_verification_notes'  => $notes,
            ]);
        } catch (\Throwable $e) {
            Log::warning('IdDocumentAIVerificationService falló', [
                'document_id' => $document->id,
                'error'       => $e->getMessage(),
            ]);
            $document->update([
                'ai_verification_status' => 'error',
                'ai_verification_notes'  => 'No se pudo verificar automáticamente en este momento.',
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

    private function extract(string $bytes, string $mimeType): ?array
    {
        $prompt = <<<'PROMPT'
Esta imagen es una identificación oficial mexicana (INE/IFE, pasaporte, o cédula profesional). Extrae estos datos y responde ÚNICAMENTE con un JSON válido, sin texto adicional, con esta forma exacta:

{
  "legible": true|false,
  "documento_ocupa_mayoria_imagen": true|false,
  "tipo_documento": "INE" | "pasaporte" | "cedula_profesional" | "otro" | null,
  "nombre_completo": "string o null",
  "curp": "string (18 caracteres) o null",
  "fecha_nacimiento": "YYYY-MM-DD o null",
  "vigencia_mes": número 1-12 o null,
  "vigencia_anio": número YYYY o null
}

Reglas:
- "legible" es false si la imagen está borrosa, incompleta, o no es una identificación oficial — en ese caso los demás campos van null.
- "documento_ocupa_mayoria_imagen" es false si la identificación se ve chica dentro de la foto (por ejemplo, una hoja tamaño carta fotografiada completa con la credencial en el centro, dejando mucho fondo/mesa/mano alrededor). Es true solo si la identificación llena la mayor parte del encuadre, con poco margen alrededor.
- El INE normalmente solo muestra mes y año de vigencia (no día) — usa exactamente eso.
- Si el documento es una identificación mexicana pero no trae CURP visible (ej. pasaporte), deja curp en null, no lo inventes.
- No agregues explicación, comentarios ni texto fuera del JSON.
PROMPT;

        $provider = new AnthropicProvider();
        $mediaType = $mimeType === 'image/jpg' ? 'image/jpeg' : $mimeType;

        $raw = $provider->completeVision(base64_encode($bytes), $mediaType, $prompt, null, ['_service' => 'portal.id_verification']);

        // El modelo a veces envuelve el JSON en ```json ... ``` pese a la instrucción — recortar.
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /** @return array{0: string, 1: string} [status, notes] */
    private function compare(array $extracted, Client $client): array
    {
        $notes = [];
        $hasMismatch = false;

        // Nombre — comparación por tokens (no exacta: acentos, orden, nombres compuestos)
        $extractedName = $this->normalizeName($extracted['nombre_completo'] ?? '');
        $clientName    = $this->normalizeName($client->name ?? '');
        if ($extractedName && $clientName) {
            $extractedTokens = array_filter(explode(' ', $extractedName));
            $clientTokens    = array_filter(explode(' ', $clientName));
            $overlap = count(array_intersect($extractedTokens, $clientTokens));
            $minTokens = min(count($extractedTokens), count($clientTokens));
            if ($minTokens > 0 && $overlap / $minTokens < 0.6) {
                $hasMismatch = true;
                $notes[] = "Nombre no coincide (documento: \"{$extracted['nombre_completo']}\" vs registrado: \"{$client->name}\").";
            } else {
                $notes[] = 'Nombre coincide.';
            }
        }

        // CURP
        $extractedCurp = strtoupper(trim($extracted['curp'] ?? ''));
        $clientCurp    = strtoupper(trim($client->curp ?? ''));
        if ($extractedCurp && $clientCurp) {
            if ($extractedCurp !== $clientCurp) {
                $hasMismatch = true;
                $notes[] = "CURP no coincide (documento: {$extractedCurp} vs registrado: {$clientCurp}).";
            } else {
                $notes[] = 'CURP coincide.';
            }
        }

        // Vigencia
        $isExpired = false;
        $vm = $extracted['vigencia_mes'] ?? null;
        $vy = $extracted['vigencia_anio'] ?? null;
        if ($vm && $vy) {
            $expiry = \Carbon\Carbon::create((int) $vy, (int) $vm, 1)->endOfMonth();
            $isExpired = $expiry->isPast();
            $notes[] = $isExpired
                ? "Identificación vencida ({$vm}/{$vy})."
                : "Vigente hasta {$vm}/{$vy}.";
        }

        if ($isExpired) {
            $status = 'expired';
        } elseif ($hasMismatch) {
            $status = 'mismatch';
        } else {
            $status = 'match';
        }

        return [$status, implode(' ', $notes)];
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtoupper(trim($name));
        $name = strtr($name, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N']);
        return preg_replace('/\s+/', ' ', $name) ?? '';
    }
}
