<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleDriveService
{
    private GoogleClient $client;
    private GoogleDrive  $drive;

    public function __construct()
    {
        $this->client = $this->buildClient();
        $this->drive  = new GoogleDrive($this->client);
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    private function buildClient(): GoogleClient
    {
        $credentialsPath = base_path(config('services.google_drive.credentials_path'));

        if (!file_exists($credentialsPath)) {
            throw new RuntimeException(
                "Google Service Account JSON no encontrado en: {$credentialsPath}\n" .
                "Crea la carpeta storage/app/google/ y coloca el archivo service-account.json ahí."
            );
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentialsPath);
        $client->setScopes([GoogleDrive::DRIVE]);

        // Domain-wide delegation: el SA sube archivos como el admin de Workspace
        // para usar su cuota de Drive en lugar de la del SA (que es inexistente).
        $adminEmail = config('services.google_drive.admin_email');
        if ($adminEmail) {
            $client->setSubject($adminEmail);
        }

        return $client;
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    /**
     * Sube un PDF local a la carpeta de Google Drive configurada.
     * Retorna el fileId asignado por Drive.
     */
    public function uploadPdf(string $localPdfPath, string $fileName): string
    {
        if (!file_exists($localPdfPath)) {
            throw new RuntimeException("PDF no encontrado en: {$localPdfPath}");
        }

        $folderId = config('services.google_drive.folder_id');

        $fileMetadata = new DriveFile([
            'name'    => $fileName,
            'parents' => $folderId ? [$folderId] : [],
        ]);

        $file = $this->drive->files->create(
            $fileMetadata,
            [
                'data'              => file_get_contents($localPdfPath),
                'mimeType'          => 'application/pdf',
                'uploadType'        => 'multipart',
                'fields'            => 'id,name,webViewLink',
                'supportsAllDrives' => true,
            ]
        );

        Log::info('GoogleDrive: PDF subido', [
            'file_id'  => $file->getId(),
            'name'     => $file->getName(),
            'link'     => $file->getWebViewLink(),
        ]);

        return $file->getId();
    }

    // ── Download ──────────────────────────────────────────────────────────────

    /**
     * Descarga un archivo de Drive y lo guarda en la ruta de destino local.
     * Retorna true si fue exitoso.
     */
    public function downloadSignedDocument(string $fileId, string $destinationPath): bool
    {
        $directory = dirname($destinationPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        try {
            $response = $this->drive->files->get($fileId, [
                'alt'               => 'media',
                'supportsAllDrives' => true,
            ]);
            file_put_contents($destinationPath, $response->getBody()->getContents());

            Log::info('GoogleDrive: archivo descargado', [
                'file_id'     => $fileId,
                'destination' => $destinationPath,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('GoogleDrive: error al descargar archivo', [
                'file_id' => $fileId,
                'error'   => $e->getMessage(),
            ]);
            return false;
        }
    }

    // ── File info ─────────────────────────────────────────────────────────────

    /**
     * Retorna metadata básica de un archivo en Drive.
     */
    public function getFileInfo(string $fileId): array
    {
        $file = $this->drive->files->get($fileId, [
            'fields' => 'id,name,mimeType,webViewLink,createdTime,modifiedTime',
        ]);

        return [
            'id'            => $file->getId(),
            'name'          => $file->getName(),
            'mime_type'     => $file->getMimeType(),
            'web_view_link' => $file->getWebViewLink(),
            'created_at'    => $file->getCreatedTime(),
            'modified_at'   => $file->getModifiedTime(),
        ];
    }

    /**
     * Lista las carpetas disponibles en Drive (útil para google:setup).
     */
    public function listFolders(): array
    {
        $results = $this->drive->files->listFiles([
            'q'                         => "mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields'                    => 'files(id,name)',
            'includeItemsFromAllDrives' => true,
            'supportsAllDrives'         => true,
        ]);

        return array_map(fn($f) => ['id' => $f->getId(), 'name' => $f->getName()],
            $results->getFiles());
    }

    // ── Push notifications (Drive Watch) ─────────────────────────────────────

    /**
     * Registra un canal de notificaciones push para un archivo específico.
     * Google Drive llamará al webhook cada vez que el archivo cambie.
     *
     * @param  string  $fileId      ID del archivo en Drive
     * @param  string  $channelId   UUID único para este canal
     * @param  string  $webhookUrl  URL pública de tu servidor (debe ser HTTPS)
     * @return array   ['resource_id' => '...', 'expiration' => timestamp_ms]
     */
    public function watchFile(string $fileId, string $channelId, string $webhookUrl): array
    {
        $channel = new \Google\Service\Drive\Channel([
            'id'      => $channelId,
            'type'    => 'web_hook',
            'address' => $webhookUrl,
            'token'   => config('services.google_drive.webhook_secret'),
        ]);

        $watchResponse = $this->drive->files->watch($fileId, $channel);

        return [
            'resource_id' => $watchResponse->getResourceId(),
            'expiration'  => $watchResponse->getExpiration(), // timestamp en ms
        ];
    }

    public function getDriveClient(): GoogleClient
    {
        return $this->client;
    }
}
