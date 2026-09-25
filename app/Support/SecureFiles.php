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

    /**
     * Miniatura JPEG (lado mayor $maxSide) cacheada en el disco privado. Las listas de documentos mostraban
     * la foto COMPLETA (2–3 MB) por fila: lento y frágil. Devuelve la ruta absoluta de la miniatura, o null si no
     * se puede generar (sin GD, no es imagen, o demasiado grande para decodificar con seguridad de memoria).
     */
    public static function thumbnail(?string $path, int $maxSide = 240): ?string
    {
        // Una miniatura es un adorno: NUNCA debe romper la lista. Cualquier problema (sin GD, permisos, memoria)
        // devuelve null y el controlador cae a la imagen original.
        try {
            $abs = self::locate($path);
            if (! $abs || ! function_exists('imagecreatefromstring')) {
                return null;
            }

            $info = @getimagesize($abs);
            if (! $info || ! in_array($info[2] ?? 0, [IMAGETYPE_JPEG, IMAGETYPE_PNG], true) || ($info[0] * $info[1]) > 40_000_000) {
                return null;
            }

            // Caché en storage/framework/cache (siempre escribible por el usuario web), no en el disco privado:
            // así no depende de los permisos de storage/app/private. Sigue sin ser accesible por URL.
            $dir = storage_path('framework/cache/doc-thumbs');
            $file = $dir . '/' . md5($path . '|' . filemtime($abs) . '|' . $maxSide) . '.jpg';
            if (is_file($file)) {
                return $file;
            }
            if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
                return null;
            }

            $img = @imagecreatefromstring((string) file_get_contents($abs));
            if (! $img) {
                return null;
            }
            $long = max($info[0], $info[1]);
            if ($long > $maxSide) {
                $scaled = imagescale($img, (int) round($info[0] * $maxSide / $long), (int) round($info[1] * $maxSide / $long));
                if ($scaled) {
                    imagedestroy($img);
                    $img = $scaled;
                }
            }
            ob_start();
            imagejpeg($img, null, 72);
            $data = (string) ob_get_clean();
            imagedestroy($img);

            return ($data !== '' && @file_put_contents($file, $data) !== false) ? $file : null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SecureFiles::thumbnail falló; se usa la imagen original', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /** Respuesta con la miniatura (caché privada corta: son pequeñas y la lista las pide varias veces). */
    public static function thumbnailResponse(?string $path): ?BinaryFileResponse
    {
        $thumb = self::thumbnail($path);
        if (! $thumb) {
            return null;
        }
        $response = response()->file($thumb, ['Content-Type' => 'image/jpeg']);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cache-Control', 'private, max-age=600');

        return $response;
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
