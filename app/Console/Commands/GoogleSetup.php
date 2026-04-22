<?php

namespace App\Console\Commands;

use App\Services\GoogleDriveService;
use App\Services\GoogleESignatureService;
use Illuminate\Console\Command;

class GoogleSetup extends Command
{
    protected $signature   = 'google:setup';
    protected $description = 'Verifica la configuración de Google Drive y eSignature API';

    public function handle(GoogleDriveService $drive): int
    {
        $this->info('=== Google Workspace eSignature — Verificación de configuración ===');
        $this->newLine();

        // 1. Verificar credenciales
        $credPath = base_path(config('services.google_drive.credentials_path'));
        $this->checkItem(
            'service-account.json',
            file_exists($credPath),
            $credPath
        );

        // 2. Variables de entorno
        $folderId   = config('services.google_drive.folder_id');
        $adminEmail = config('services.google_drive.admin_email');

        $this->checkItem('GOOGLE_DRIVE_FOLDER_ID',     !empty($folderId),   $folderId ?: '(vacío)');
        $this->checkItem('GOOGLE_WORKSPACE_ADMIN_EMAIL', !empty($adminEmail), $adminEmail ?: '(vacío)');

        $this->newLine();

        // 3. Probar conectividad con Drive API
        $this->line('<comment>Probando conectividad con Google Drive API...</comment>');
        try {
            $folders = $drive->listFolders();
            $this->info('  ✓ Conexión exitosa.');

            if (empty($folders)) {
                $this->warn('  ⚠ No se encontraron carpetas compartidas con el Service Account.');
                $this->warn('    Comparte la carpeta de Drive con el email del SA.');
            } else {
                $this->info('  Carpetas disponibles (copia el ID que quieras usar como GOOGLE_DRIVE_FOLDER_ID):');
                $this->newLine();
                $headers = ['ID', 'Nombre'];
                $rows    = array_map(fn($f) => [$f['id'], $f['name']], $folders);
                $this->table($headers, $rows);
            }
        } catch (\Throwable $e) {
            $this->error("  ✗ Error de conexión: {$e->getMessage()}");
            $this->newLine();
            $this->line('  <comment>Pasos para solucionar:</comment>');
            $this->line('  1. Verifica que el JSON del Service Account es válido');
            $this->line('  2. En Google Cloud Console: habilita "Google Drive API"');
            $this->line('  3. En Google Workspace Admin > Seguridad > Delegación de dominio:');
            $this->line('     Agrega el Client ID del SA con scope: https://www.googleapis.com/auth/drive');
            $this->line('  4. En GOOGLE_WORKSPACE_ADMIN_EMAIL pon tu email de admin de Workspace');
        }

        $this->newLine();
        $this->line('=== Fin de la verificación ===');

        return self::SUCCESS;
    }

    private function checkItem(string $label, bool $ok, string $value): void
    {
        $icon   = $ok ? '✓' : '✗';
        $method = $ok ? 'info' : 'error';
        $this->{$method}("  {$icon} {$label}: {$value}");
    }
}
