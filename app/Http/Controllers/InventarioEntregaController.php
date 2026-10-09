<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\RentalProcess;
use App\Services\InventarioEntregaGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class InventarioEntregaController extends Controller
{
    public function generar(Request $request, RentalProcess $rental, InventarioEntregaGeneratorService $generator)
    {
        $validated = $request->validate([
            'items_detalle' => 'required|string|max:8000',
            'lectura_luz' => 'nullable|string|max:50',
            'lectura_gas' => 'nullable|string|max:50',
            'lectura_agua' => 'nullable|string|max:50',
            'llaves_recamaras' => 'nullable|string|max:50',
            'llaves_entrada' => 'nullable|string|max:50',
            'chips_acceso' => 'nullable|string|max:50',
            'controles_estacionamiento' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:2000',
            'fecha_entrega' => 'nullable|date',
        ]);

        try {
            $path = $generator->generatePdf(
                $rental,
                $validated['items_detalle'],
                $validated['lectura_luz'] ?? null,
                $validated['lectura_gas'] ?? null,
                $validated['lectura_agua'] ?? null,
                $validated['llaves_recamaras'] ?? null,
                $validated['llaves_entrada'] ?? null,
                $validated['chips_acceso'] ?? null,
                $validated['controles_estacionamiento'] ?? null,
                $validated['observaciones'] ?? null,
                $validated['fecha_entrega'] ?? null
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar el Inventario de Entrega: ' . $e->getMessage());
        }

        $fechaLabel = $validated['fecha_entrega'] ? \Illuminate\Support\Carbon::parse($validated['fecha_entrega'])->format('d/m/Y') : now()->format('d/m/Y');

        Document::create([
            'rental_process_id' => $rental->id,
            'client_id'    => $rental->tenant_client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'inventario_entrega',
            'label'        => 'Inventario de Entrega — ' . $fechaLabel,
            'file_path'    => $path,
            'file_name'    => 'INV-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return back()->with('success', 'Inventario de Entrega generado correctamente.');
    }

    public function pdf(RentalProcess $rental)
    {
        $document = Document::where('rental_process_id', $rental->id)
            ->where('category', 'inventario_entrega')
            ->latest()
            ->first();

        if (!$document || !file_exists($document->file_path)) {
            abort(404, 'PDF no encontrado.');
        }

        return Response::make(file_get_contents($document->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="inventario-entrega.pdf"',
        ]);
    }
}
