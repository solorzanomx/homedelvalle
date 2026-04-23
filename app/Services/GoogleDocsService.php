<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Docs as GoogleDocs;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

/**
 * Genera documentos de Google Docs a partir de un template.
 *
 * Flujo:
 *  1. Se tiene un Google Doc template en Drive con placeholders: {{NOMBRE_CLIENTE}}, etc.
 *  2. El servicio copia el template a la carpeta de destino en Drive.
 *  3. Reemplaza todos los placeholders con los datos reales del cliente.
 *  4. Retorna el fileId del documento generado.
 *
 * PREREQUISITO MANUAL:
 *  - Crea el template en Google Docs (dentro de la Unidad Compartida HDV-Contratos
 *    o compartido con home-del-valle@tokyo-silicon-360922.iam.gserviceaccount.com)
 *  - Copia el ID del documento y ponlo en GOOGLE_DOCS_TEMPLATE_ID_CONFIDENCIALIDAD
 *  - Placeholders en el template deben estar en formato: {{NOMBRE_CLIENTE}}
 */
class GoogleDocsService
{
    private GoogleClient $client;
    private GoogleDocs   $docs;
    private GoogleDrive  $drive;

    public function __construct()
    {
        $this->client = $this->buildClient();
        $this->docs   = new GoogleDocs($this->client);
        $this->drive  = new GoogleDrive($this->client);
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    private function buildClient(): GoogleClient
    {
        $credentialsPath = base_path(config('services.google_drive.credentials_path'));

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([
            GoogleDrive::DRIVE,
            GoogleDocs::DOCUMENTS,
        ]);

        return $client;
    }

    // ── Template → Documento ─────────────────────────────────────────────────

    /**
     * Copia un template de Google Docs y reemplaza los placeholders con los datos dados.
     *
     * @param  string  $templateId    ID del Google Doc template en Drive
     * @param  string  $documentName  Nombre del documento generado
     * @param  array   $replacements  ['{{NOMBRE_CLIENTE}}' => 'Juan García', ...]
     * @param  string|null $folderId  Carpeta destino (usa GOOGLE_DRIVE_FOLDER_ID si es null)
     * @return string  fileId del documento generado
     */
    public function createFromTemplate(
        string  $templateId,
        string  $documentName,
        array   $replacements,
        ?string $folderId = null
    ): string {
        $folderId = $folderId ?? config('services.google_drive.folder_id');

        // 1. Copiar el template
        $copy = new DriveFile([
            'name'    => $documentName,
            'parents' => $folderId ? [$folderId] : [],
        ]);

        $newFile = $this->drive->files->copy($templateId, $copy, [
            'supportsAllDrives' => true,
            'fields'            => 'id,name,webViewLink',
        ]);

        $newFileId = $newFile->getId();

        Log::info('GoogleDocs: template copiado', [
            'template_id' => $templateId,
            'new_file_id' => $newFileId,
            'name'        => $documentName,
        ]);

        // 2. Reemplazar placeholders con batchUpdate
        if (!empty($replacements)) {
            $this->replaceText($newFileId, $replacements);
        }

        return $newFileId;
    }

    /**
     * Reemplaza placeholders en un documento existente usando Docs API batchUpdate.
     *
     * @param  string  $fileId        ID del Google Doc a modificar
     * @param  array   $replacements  ['{{PLACEHOLDER}}' => 'valor', ...]
     */
    public function replaceText(string $fileId, array $replacements): void
    {
        $requests = [];

        foreach ($replacements as $placeholder => $value) {
            $requests[] = new \Google\Service\Docs\Request([
                'replaceAllText' => new \Google\Service\Docs\ReplaceAllTextRequest([
                    'containsText' => new \Google\Service\Docs\SubstringMatchCriteria([
                        'text'      => $placeholder,
                        'matchCase' => true,
                    ]),
                    'replaceText' => (string) $value,
                ]),
            ]);
        }

        $batchUpdate = new \Google\Service\Docs\BatchUpdateDocumentRequest([
            'requests' => $requests,
        ]);

        $this->docs->documents->batchUpdate($fileId, $batchUpdate);

        Log::info('GoogleDocs: placeholders reemplazados', [
            'file_id' => $fileId,
            'count'   => count($replacements),
        ]);
    }

    /**
     * Retorna la URL para ver el documento en Google Docs.
     */
    public function viewUrl(string $fileId): string
    {
        return "https://docs.google.com/document/d/{$fileId}/edit";
    }

    /**
     * Exporta el Google Doc como PDF y retorna el contenido binario.
     * Útil para guardar una copia PDF localmente después de generado.
     */
    public function exportAsPdf(string $fileId): string
    {
        $response = $this->drive->files->export($fileId, 'application/pdf', [
            'supportsAllDrives' => true,
        ]);

        return $response->getBody()->getContents();
    }
}
