<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyQrCode;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;
use Exception;

class PropertyQrService
{
    private const QR_DISK = 'public';
    private const QR_PATH_PREFIX = 'qr-codes/properties';
    private const QR_FILENAME = 'qr.png';
    private const QR_SIZE = 300; // pixels

    /**
     * Generar o reutilizar QR code para una propiedad
     *
     * @param Property $property
     * @param bool $forceRegenerate Si true, siempre regenera sin verificar
     * @return PropertyQrCode
     * @throws Exception
     */
    public function generateOrReuse(Property $property, bool $forceRegenerate = false): PropertyQrCode
    {
        // Obtener la URL pública de la propiedad
        $publicUrl = $this->getPropertyPublicUrl($property);

        // Verificar si existe QR y si es válido
        if (!$forceRegenerate && $property->qrCode) {
            // QR existe, verificar si URL cambió
            if (!$property->qrCode->needsRegeneration($publicUrl)) {
                // URL no cambió, reutilizar
                return $property->qrCode;
            }
        }

        // Necesita regeneración, crear nuevo QR
        return $this->generate($property, $publicUrl);
    }

    /**
     * Generar nuevo QR code
     *
     * @param Property $property
     * @param string|null $url URL a codificar (si null, usa la pública de property)
     * @return PropertyQrCode
     * @throws Exception
     */
    public function generate(Property $property, ?string $url = null): PropertyQrCode
    {
        $url = $url ?? $this->getPropertyPublicUrl($property);

        // Generar imagen QR con la API v6
        $qrCode = new QrCode($url);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setSize(self::QR_SIZE);

        $pngWriter = new PngWriter();
        $result = $pngWriter->write($qrCode);

        // Preparar ruta de almacenamiento
        $qrPath = $this->getQrStoragePath($property);
        $directory = dirname($qrPath);

        // Crear directorio si no existe
        if (!Storage::disk(self::QR_DISK)->exists($directory)) {
            Storage::disk(self::QR_DISK)->makeDirectory($directory);
        }

        // Guardar archivo
        Storage::disk(self::QR_DISK)->put(
            $qrPath,
            $result->getStream()->getContents()
        );

        // Guardar o actualizar registro en BD
        $qrCode = PropertyQrCode::updateOrCreate(
            ['property_id' => $property->id],
            [
                'qr_code_path' => $qrPath,
                'qr_url' => $url,
                'generated_at' => now(),
            ]
        );

        return $qrCode;
    }

    /**
     * Obtener URL pública de la propiedad
     * Usa easybroker_public_url si existe, sino construye una URL local
     *
     * @param Property $property
     * @return string
     */
    private function getPropertyPublicUrl(Property $property): string
    {
        // Si tiene URL pública de EasyBroker, usarla
        if ($property->easybroker_public_url) {
            return $property->easybroker_public_url;
        }

        // Construir URL local basada en la ruta de la propiedad
        return route('propiedades.show', [
            'id' => $property->id,
            'slug' => $property->slug,
        ]);
    }

    /**
     * Obtener ruta de almacenamiento del QR para una propiedad
     *
     * @param Property $property
     * @return string
     */
    private function getQrStoragePath(Property $property): string
    {
        return self::QR_PATH_PREFIX . '/' . $property->id . '/' . self::QR_FILENAME;
    }

    /**
     * Obtener URL pública del QR (para acceso web)
     *
     * @param PropertyQrCode $qrCode
     * @return string|null
     */
    public function getPublicUrl(PropertyQrCode $qrCode): ?string
    {
        if (!$qrCode->qr_code_path) {
            return null;
        }

        return Storage::disk(self::QR_DISK)->url($qrCode->qr_code_path);
    }

    /**
     * Obtener QR como SVG
     * Útil para impresión y escalado
     *
     * @param PropertyQrCode $qrCode
     * @return string (SVG content)
     * @throws Exception
     */
    public function getAsSvg(PropertyQrCode $qrCode): string
    {
        $qr = new QrCode($qrCode->qr_url);
        $qr->setEncoding('UTF-8');
        $qr->setSize(self::QR_SIZE);

        $svgWriter = new SvgWriter();
        $result = $svgWriter->write($qr);

        return $result->getStream()->getContents();
    }

    /**
     * Regenerar QR de una propiedad
     *
     * @param Property $property
     * @return PropertyQrCode
     * @throws Exception
     */
    public function regenerate(Property $property): PropertyQrCode
    {
        // Eliminar archivo anterior si existe
        if ($property->qrCode && $property->qrCode->qr_code_path) {
            Storage::disk(self::QR_DISK)->delete($property->qrCode->qr_code_path);
        }

        // Generar nuevo
        return $this->generate($property);
    }

    /**
     * Eliminar QR de una propiedad
     *
     * @param Property $property
     * @return bool
     */
    public function delete(Property $property): bool
    {
        if (!$property->qrCode) {
            return false;
        }

        // Eliminar archivo
        if ($property->qrCode->qr_code_path) {
            Storage::disk(self::QR_DISK)->delete($property->qrCode->qr_code_path);
        }

        // Eliminar registro BD
        return $property->qrCode->delete();
    }
}
