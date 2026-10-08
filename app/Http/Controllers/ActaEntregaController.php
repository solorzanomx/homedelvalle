<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Operation;
use App\Services\ActaEntregaGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ActaEntregaController extends Controller
{
    public function generar(Request $request, Operation $operation, ActaEntregaGeneratorService $generator)
    {
        if (!$operation->secondaryClient) {
            return back()->with('error', 'Esta Operation no tiene un comprador vinculado (secondary_client_id) — no se puede generar el Acta.');
        }

        $validated = $request->validate([
            'juegos_llaves' => 'nullable|integer|min:1|max:10',
            'co_comprador_nombre' => 'nullable|string|max:150',
        ]);
        $juegosLlaves = $validated['juegos_llaves'] ?? 2;
        $coCompradorNombre = $validated['co_comprador_nombre'] ?? null;

        try {
            $path = $generator->generatePdf($operation, $juegosLlaves, $coCompradorNombre);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar el Acta de Entrega: ' . $e->getMessage());
        }

        Document::create([
            'operation_id' => $operation->id,
            'client_id'    => $operation->secondary_client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'acta_entrega',
            'label'        => 'Acta de Entrega — ' . now()->format('d/m/Y'),
            'file_path'    => $path,
            'file_name'    => 'AE-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return back()->with('success', 'Acta de Entrega generada correctamente.');
    }

    public function pdf(Operation $operation)
    {
        $document = Document::where('operation_id', $operation->id)
            ->where('category', 'acta_entrega')
            ->latest()
            ->first();

        if (!$document || !file_exists($document->file_path)) {
            abort(404, 'PDF no encontrado.');
        }

        return Response::make(file_get_contents($document->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="acta-entrega.pdf"',
        ]);
    }
}
