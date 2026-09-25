<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Almacenamiento PRIVADO de archivos sensibles (INE, estados de cuenta, escrituras, contratos…),
 * 2026-09-26. Antes se guardaban en el disco `public` (storage/app/public → /storage/...): quien
 * tuviera o adivinara la URL exacta veía el archivo SIN iniciar sesión. Ahora van al disco `local`
 * (storage/app/private, sin URL pública) y solo se sirven por rutas con autorización.
 *
 * `locate()` conserva una RESOLUCIÓN DE TRES NIVELES para poder migrar sin apagar nada:
 *   1. ruta absoluta (PDFs que el sistema genera en storage/app/…)
 *   2. disco privado
 *   3. disco público LEGACY (archivos subidos antes del cambio, hasta correr `files:secure-migrate`)
 */
class SecureFiles
{
    public const DISK = 'local';

    /** Guarda un archivo subido en el disco privado; devuelve la ruta relativa para la BD. */
    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, self::DISK);
    }

    /** Escribe contenido (p. ej. el PDF de un contrato) en el disco privado. */
    public static function put(string $path, string $contents): void
    {
        Storage::disk(self::DISK)->put($path, $contents);
    }

    /** Ruta absoluta real del archivo, o null si no existe en ningún lado. */
    public static function locate(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, '/')) {
            return is_file($path) ? $path : null;
        }
        if (Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->path($path);
        }
        if (Storage::disk('public')->exists($path)) { // legacy
            return Storage::disk('public')->path($path);
        }

        return null;
    }

    public static function exists(?string $path): bool
    {
        return self::locate($path) !== null;
    }

    public static function get(?string $path): ?string
    {
        $abs = self::locate($path);

        return $abs ? file_get_contents($abs) : null;
    }

    /** ¿Sigue en el disco público (sin migrar)? */
    public static function isLegacyPublic(?string $path): bool
    {
        return $path && ! str_starts_with($path, '/') && ! Storage::disk(self::DISK)->exists($path) && Storage::disk('public')->exists($path);
    }

    /** Borra el archivo de donde esté (privado y/o público legacy). Nunca toca rutas absolutas del sistema. */
    public static function delete(?string $path): void
    {
        if (! $path || str_starts_with($path, '/')) {
            return;
        }
        Storage::disk(self::DISK)->delete($path);
        Storage::disk('public')->delete($path);
    }

    /** Respuesta de descarga/visor con cabeceras seguras (sin caché compartida, sin adivinar el tipo). */
    public static function response(?string $path, ?string $name = null, ?string $mime = null, bool $inline = false): ?BinaryFileResponse
    {
        $abs = self::locate($path);
        if (! $abs) {
            return null;
        }
        $name = $name ?: basename($abs);

        $response = response()->file($abs, array_filter(['Content-Type' => $mime]));
        $response->setContentDisposition($inline ? 'inline' : 'attachment', $name, str_replace(['"', '\\', '/'], '_', \Illuminate\Support\Str::ascii($name)));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
