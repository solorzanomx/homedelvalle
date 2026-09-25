<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractVersion;
use App\Models\Document;
use App\Support\SecureFiles;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureMigrateFiles extends Command
{
    protected $signature = 'files:secure-migrate {--dry-run : Solo reporta, no mueve nada} {--keep-public : Copia al disco privado pero NO borra el original público} {--include-orphans : Además mueve al disco privado archivos en las carpetas sensibles del disco público que ninguna fila de la BD referencia}';

    protected $description = 'Mueve documentos y contratos ya subidos del disco PÚBLICO (URL directa) al disco PRIVADO (solo por rutas autorizadas)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $keep = (bool) $this->option('keep-public');
        $public = Storage::disk('public');
        $private = Storage::disk(SecureFiles::DISK);

        $paths = collect()
            ->merge(Document::whereNotNull('file_path')->pluck('file_path'))
            ->merge(Contract::whereNotNull('pdf_path')->pluck('pdf_path'))
            ->merge(ContractVersion::whereNotNull('pdf_path')->pluck('pdf_path'))
            ->filter(fn($p) => is_string($p) && $p !== '' && ! str_starts_with($p, '/'))
            ->unique()->values();

        $moved = $already = $missing = $failed = 0;
        foreach ($paths as $path) {
            if ($private->exists($path)) {
                $already++;
                // Ya está protegido; si quedó una copia pública vieja, se retira (salvo --keep-public).
                if ($public->exists($path) && ! $dry && ! $keep) {
                    $public->delete($path);
                }
                continue;
            }
            if (! $public->exists($path)) {
                $missing++;
                $this->warn("  sin archivo: {$path}");
                continue;
            }
            if ($dry) {
                $moved++;
                $this->line("  [dry-run] movería: {$path}");
                continue;
            }

            $stream = $public->readStream($path);
            $private->writeStream($path, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            // Solo se borra el original si la copia existe y pesa lo mismo.
            if ($private->exists($path) && $private->size($path) === $public->size($path)) {
                if (! $keep) {
                    $public->delete($path);
                }
                $moved++;
            } else {
                $failed++;
                $this->error("  FALLÓ la verificación: {$path} (el original público se conserva)");
            }
        }

        // Archivos sueltos en las carpetas sensibles del disco público que la BD ya no referencia (restos de
        // borrados viejos): siguen siendo públicos por URL. Se reportan; con --include-orphans se mueven.
        $known = $paths->flip();
        $orphans = collect(['documents', 'expediente', 'captaciones', 'contracts'])
            ->flatMap(fn($dir) => $public->allFiles($dir))
            ->reject(fn($f) => $known->has($f))->values();
        $orphansMoved = 0;
        if ($orphans->isNotEmpty()) {
            $this->warn("  {$orphans->count()} archivo(s) huérfano(s) en el disco público (sin fila en la BD)" . ($this->option('include-orphans') ? '' : ' — usa --include-orphans para moverlos'));
            if ($this->option('include-orphans') && ! $dry) {
                foreach ($orphans as $f) {
                    $private->writeStream($f, $public->readStream($f));
                    if ($private->exists($f) && $private->size($f) === $public->size($f)) {
                        $public->delete($f);
                        $orphansMoved++;
                    }
                }
            }
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Referencias: {$paths->count()} · movidos: {$moved} · ya privados: {$already} · sin archivo: {$missing} · fallidos: {$failed} · huérfanos: {$orphans->count()} (movidos: {$orphansMoved})");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
