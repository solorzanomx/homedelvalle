<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mifiel\ApiClient;
use Mifiel\Document;

class MifielService
{
    public function __construct()
    {
        $this->configure();
    }

    // ── Configuración del SDK ─────────────────────────────────────────────

    private function configure(): void
    {
        $appId     = config('services.mifiel.app_id');
        $appSecret = config('services.mifiel.app_secret');
        $sandbox   = config('services.mifiel.sandbox', true);

        ApiClient::setTokens($appId, $appSecret);

        $url = $sandbox
            ? config('services.mifiel.url_sandbox')
            : config('services.mifiel.url_production');

        ApiClient::url($url);
    }

    // ── Métodos públicos ──────────────────────────────────────────────────

    /**
     * Envía un PDF a Mifiel para firma electrónica.
     *
     * @param  string  $pdfPath      Ruta absoluta al PDF (storage_path o public_path)
     * @param  array   $signers      [['name'=>'', 'email'=>'', 'tax_id'=>''], ...]
     * @param  string  $callbackUrl  URL donde Mifiel enviará el webhook al firmar
     * @return array   ['document_id'=>'', 'signers'=>[], 'widget_id'=>'', 'original_hash'=>'']
     *
     * @throws \RuntimeException si Mifiel devuelve error
     */
    public function sendDocument(string $pdfPath, array $signers, string $callbackUrl): array
    {
        if (!file_exists($pdfPath)) {
            throw new \RuntimeException("PDF no encontrado en: {$pdfPath}");
        }

        $document = new Document([
            'file_path'    => $pdfPath,
            'callback_url' => $callbackUrl,
            'signatories'  => $this->formatSigners($signers),
        ]);

        $document->save();

        Log::info('MifielService: documento enviado', [
            'document_id' => $document->id,
            'signers'     => count($signers),
            'file'        => basename($pdfPath),
        ]);

        return [
            'document_id'   => $document->id,
            'widget_id'     => $document->widget_id ?? null,
            'original_hash' => $document->original_hash ?? null,
            'signers'       => $document->signatories ?? [],
            'created_at'    => $document->created_at ?? null,
        ];
    }

    /**
     * Consulta el estado actual de un documento en Mifiel.
     *
     * @return array Con todos los campos que Mifiel devuelve (id, status, signers, etc.)
     */
    public function getDocument(string $documentId): array
    {
        $document = Document::find($documentId);

        return (array) $document->values ?? [];
    }

    /**
     * Descarga el PDF firmado de Mifiel y lo guarda en storage/app/.
     *
     * @param  string  $documentId       ID del documento en Mifiel
     * @param  string  $destinationPath  Ruta relativa dentro de storage/app/ (ej: 'mifiel/firmados/doc.pdf')
     * @return bool    true si se descargó correctamente
     */
    public function downloadSigned(string $documentId, string $destinationPath): bool
    {
        $absolutePath = storage_path('app/' . $destinationPath);

        // Asegurar que el directorio exista
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $document = Document::find($documentId);
        $document->saveFileSigned($absolutePath);

        $exists = file_exists($absolutePath) && filesize($absolutePath) > 0;

        if ($exists) {
            Log::info('MifielService: PDF firmado descargado', [
                'document_id' => $documentId,
                'path'        => $destinationPath,
                'size'        => filesize($absolutePath),
            ]);
        } else {
            Log::warning('MifielService: el PDF firmado quedó vacío', [
                'document_id' => $documentId,
                'path'        => $absolutePath,
            ]);
        }

        return $exists;
    }

    /**
     * Verifica que el webhook recibido proviene realmente de Mifiel.
     *
     * Mifiel firma el payload con HMAC-SHA256 usando el webhook_secret.
     * El header puede venir como X-Mifiel-Signature o Authorization.
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret = config('services.mifiel.webhook_secret');

        // Si no hay secret configurado, se permite en sandbox
        if (empty($secret)) {
            if (config('services.mifiel.sandbox')) {
                Log::warning('MifielService: MIFIEL_WEBHOOK_SECRET no configurado — aceptando en sandbox');
                return true;
            }
            Log::error('MifielService: MIFIEL_WEBHOOK_SECRET no configurado en producción');
            return false;
        }

        $signature = $request->header('X-Mifiel-Signature')
            ?? $request->header('X-Signature')
            ?? null;

        if (!$signature) {
            Log::warning('MifielService: webhook sin header de firma');
            return false;
        }

        $payload  = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        $valid = hash_equals($expected, $signature);

        if (!$valid) {
            Log::warning('MifielService: firma de webhook inválida', [
                'expected' => substr($expected, 0, 8) . '...',
                'received' => substr($signature, 0, 8) . '...',
            ]);
        }

        return $valid;
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    /**
     * Convierte el array de firmantes al formato que espera Mifiel.
     * Mifiel llama a este campo "signatories".
     */
    private function formatSigners(array $signers): array
    {
        return array_map(function (array $signer) {
            return array_filter([
                'name'   => $signer['name']   ?? '',
                'email'  => $signer['email']  ?? '',
                'tax_id' => $signer['tax_id'] ?? null, // RFC — opcional
            ]);
        }, $signers);
    }
}
