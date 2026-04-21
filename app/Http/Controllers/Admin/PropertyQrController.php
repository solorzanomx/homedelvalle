<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PropertyQrService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyQrController extends Controller
{
    public function __construct(
        private PropertyQrService $qrService
    ) {}

    /**
     * Generar o regenerar QR de una propiedad
     * POST /admin/properties/{property}/qr/generate
     */
    public function generate(Request $request, Property $property)
    {
        try {
            $forceRegenerate = $request->boolean('force', false);
            $qrCode = $this->qrService->generateOrReuse($property, $forceRegenerate);

            return back()->with('success', 'QR code generado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar QR: ' . $e->getMessage());
        }
    }

    /**
     * Descargar QR de una propiedad
     * GET /admin/properties/{property}/qr/download?format=png|svg
     */
    public function download(Request $request, Property $property): StreamedResponse|Response
    {
        if (!$property->qrCode) {
            // Generar si no existe
            try {
                $property->qrCode = $this->qrService->generateOrReuse($property);
            } catch (\Exception $e) {
                return back()->with('error', 'Error al generar QR: ' . $e->getMessage());
            }
        }

        $format = $request->get('format', 'png');
        $filename = "propiedad-{$property->id}-qr." . ($format === 'svg' ? 'svg' : 'png');

        if ($format === 'svg') {
            $content = $this->qrService->getAsSvg($property->qrCode);
            $mimeType = 'image/svg+xml';
        } else {
            // PNG desde el archivo guardado
            $content = \Illuminate\Support\Facades\Storage::disk('public')->get($property->qrCode->qr_code_path);
            $mimeType = 'image/png';
        }

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => $mimeType]
        );
    }

    /**
     * Eliminar QR de una propiedad
     * DELETE /admin/properties/{property}/qr
     */
    public function delete(Property $property)
    {
        try {
            $this->qrService->delete($property);
            return back()->with('success', 'QR code eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar QR: ' . $e->getMessage());
        }
    }
}
