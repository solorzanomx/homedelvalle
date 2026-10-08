<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Operation;
use App\Services\ReciboPagoParcialGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReciboPagoParcialController extends Controller
{
    public function generar(Request $request, Operation $operation, ReciboPagoParcialGeneratorService $generator)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|max:255',
            'fecha_recibo' => 'nullable|date',
        ]);

        try {
            $path = $generator->generatePdf($operation, (float) $validated['monto'], $validated['metodo_pago'], $validated['fecha_recibo'] ?? null);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar el Recibo de Pago Parcial: ' . $e->getMessage());
        }

        $fechaLabel = $validated['fecha_recibo'] ? \Illuminate\Support\Carbon::parse($validated['fecha_recibo'])->format('d/m/Y') : now()->format('d/m/Y');

        Document::create([
            'operation_id' => $operation->id,
            'client_id'    => $operation->client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'recibo_pago_parcial',
            'label'        => 'Recibo de Pago Parcial — $' . number_format((float) $validated['monto'], 2) . ' — ' . $fechaLabel,
            'file_path'    => $path,
            'file_name'    => 'RPP-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT) . '-' . now()->format('YmdHis') . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return back()->with('success', 'Recibo de Pago Parcial generado correctamente.');
    }

    public function pdf(Operation $operation, Document $document)
    {
        if ($document->operation_id !== $operation->id || $document->category !== 'recibo_pago_parcial') {
            abort(404);
        }
        if (!file_exists($document->file_path)) {
            abort(404, 'PDF no encontrado.');
        }

        return Response::make(file_get_contents($document->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="recibo-pago-parcial.pdf"',
        ]);
    }
}
