<?php

namespace App\Http\Controllers\PDF;

use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PropertySheetController extends Controller
{
    /**
     * Genera la ficha técnica en PDF de una propiedad
     *
     * Parámetros:
     * - property_id: ID de la propiedad
     * - include_broker: boolean (true/false) para incluir datos del broker
     * - broker_id: ID opcional del broker si no es el propietario de la propiedad
     *
     * Ejemplo de rutas:
     * - GET /properties/{id}/pdf → PDF sin broker
     * - GET /properties/{id}/pdf?include_broker=1 → PDF con broker actual
     * - GET /properties/{id}/pdf?include_broker=1&broker_id={id} → PDF con broker específico
     */
    public function downloadPropertySheet(Property $property, Request $request)
    {
        // Obtener la bandera para incluir broker
        $includeBroker = $request->boolean('include_broker', false);

        // Obtener el broker/asesor
        $broker = null;

        if ($includeBroker) {
            // Si se especifica un broker_id distinto, usarlo
            if ($request->has('broker_id') && $request->get('broker_id')) {
                $broker = User::findOrFail($request->get('broker_id'));
            }
            // Si no, usar el usuario autenticado (broker actual)
            elseif (auth()->check()) {
                $broker = auth()->user();
            }
        }

        // Preparar datos para la vista
        $data = [
            'property' => $property,
            'broker' => $broker,
            'includeBroker' => $includeBroker,
        ];

        // Generar PDF
        $pdf = Pdf::loadView('pdf.property-sheet', $data)
            ->setPaper('a4')
            ->setOption('enable_html5_parser', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('enable_local_file_access', true)
            ->setOption('margin_top', 0)
            ->setOption('margin_right', 0)
            ->setOption('margin_bottom', 0)
            ->setOption('margin_left', 0)
            ->setOption('dpi', 300)
            ->setOption('defaultFont', 'Segoe UI');

        // Nombre del archivo
        $filename = 'ficha-tecnica-' . $property->slug . '-' . now()->format('Y-m-d') . '.pdf';

        // Descargar o mostrar
        return $pdf->download($filename);
    }

    /**
     * Muestra la vista previa del PDF en el navegador
     */
    public function previewPropertySheet(Property $property, Request $request)
    {
        $includeBroker = $request->boolean('include_broker', false);

        $broker = null;
        if ($includeBroker) {
            if ($request->has('broker_id') && $request->get('broker_id')) {
                $broker = User::findOrFail($request->get('broker_id'));
            } elseif (auth()->check()) {
                $broker = auth()->user();
            }
        }

        $data = [
            'property' => $property,
            'broker' => $broker,
            'includeBroker' => $includeBroker,
        ];

        $pdf = Pdf::loadView('pdf.property-sheet', $data)
            ->setPaper('a4')
            ->setOption('enable_html5_parser', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('enable_local_file_access', true)
            ->setOption('margin_top', 0)
            ->setOption('margin_right', 0)
            ->setOption('margin_bottom', 0)
            ->setOption('margin_left', 0)
            ->setOption('dpi', 300)
            ->setOption('defaultFont', 'Segoe UI');

        return $pdf->stream('preview.pdf');
    }
}
