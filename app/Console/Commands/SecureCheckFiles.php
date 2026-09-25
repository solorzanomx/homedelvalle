<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Support\SecureFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureCheckFiles extends Command
{
    protected $signature = 'files:secure-check {--limit=8 : Cuántos documentos recientes revisar}';

    protected $description = 'Diagnóstico del almacenamiento privado: permisos del usuario actual, GD y si los documentos recientes se pueden leer. Correr COMO el usuario del servidor web (p. ej. sudo -u www php artisan files:secure-check)';

    public function handle(): int
    {
        $user = function_exists('posix_getpwuid') ? (posix_getpwuid(posix_geteuid())['name'] ?? '?') : get_current_user();
        $this->info("Usuario que corre este comando: {$user}  (debe ser el mismo del servidor web: www / www-data)");

        $private = Storage::disk(SecureFiles::DISK)->path('');
        $this->line('Disco privado: ' . $private);
        $this->line('  existe: ' . (is_dir($private) ? 'sí' : 'NO') . ' · escribible: ' . (is_writable($private) ? 'sí' : 'NO  ← problema de permisos'));
        $docs = rtrim($private, '/') . '/documents';
        if (is_dir($docs)) {
            $this->line('  documents/: escribible: ' . (is_writable($docs) ? 'sí' : 'NO  ← problema de permisos') . ' · dueño: ' . (function_exists('posix_getpwuid') ? (posix_getpwuid(fileowner($docs))['name'] ?? '?') : '?'));
        }
        $thumbs = storage_path('framework/cache');
        $this->line('Caché de miniaturas: ' . $thumbs . ' · escribible: ' . (is_writable($thumbs) ? 'sí' : 'NO  ← problema de permisos'));
        $this->line('GD (miniaturas): ' . (function_exists('imagecreatefromstring') ? 'sí' : 'NO  (se mostrará la foto completa)') . ' · memory_limit: ' . ini_get('memory_limit'));

        $this->newLine();
        $this->info('Documentos recientes:');
        $bad = 0;
        foreach (Document::latest('id')->take((int) $this->option('limit'))->get() as $d) {
            $abs = SecureFiles::locate($d->file_path);
            $where = ! $abs ? 'NO ENCONTRADO' : (SecureFiles::isLegacyPublic($d->file_path) ? 'público (sin migrar)' : (str_starts_with($d->file_path, '/') ? 'ruta absoluta' : 'privado'));
            $readable = $abs && is_readable($abs);
            $bad += ($abs && $readable) ? 0 : 1;
            $this->line(sprintf('  #%d %-22s %-22s legible: %s', $d->id, $d->category, $where, $readable ? 'sí' : ($abs ? 'NO ← permisos del archivo' : '—')));
        }

        $this->newLine();
        $bad ? $this->error("{$bad} documento(s) con problema. Si son de permisos: chown -R <usuario web>: storage && chmod -R ug+rwX storage") : $this->info('Todo en orden.');

        return $bad ? self::FAILURE : self::SUCCESS;
    }
}
