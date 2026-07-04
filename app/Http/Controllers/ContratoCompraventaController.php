<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Operation;
use App\Services\ContratoCompraventaGeneratorService;
use App\Services\OperationChecklistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ContratoCompraventaController extends Controller
{
    public function generar(Operation $operation, ContratoCompraventaGeneratorService $generator)
    {
        if (!$operation->secondaryClient) {
            return back()->with('error', 'Esta Operation no tiene un comprador vinculado (secondary_client_id) — no se puede generar el contrato.');
        }

        try {
            $path = $generator->generatePdf($operation);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar el contrato: ' . $e->getMessage());
        }

        Document::create([
            'operation_id' => $operation->id,
            'client_id'    => $operation->secondary_client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'contrato_compraventa',
            'label'        => 'Contrato de Compraventa — ' . now()->format('d/m/Y'),
            'file_path'    => $path,
            'file_name'    => 'CV-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return back()->with('success', 'Contrato de Compraventa generado correctamente.');
    }

    public function pdf(Operation $operation)
    {
        $document = Document::where('operation_id', $operation->id)
            ->where('category', 'contrato_compraventa')
            ->latest()
            ->first();

        if (!$document || !file_exists($document->file_path)) {
            abort(404, 'PDF no encontrado.');
        }

        return Response::make(file_get_contents($document->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato-compraventa.pdf"',
        ]);
    }

    public function markSigned(Operation $operation, OperationChecklistService $checklistService)
    {
        $document = Document::where('operation_id', $operation->id)
            ->where('category', 'contrato_compraventa')
            ->exists();

        if (!$document) {
            return back()->with('error', 'No hay contrato de compraventa generado.');
        }

        if ($operation->stage === 'contrato') {
            $checklistService->changeStage($operation, 'entrega', Auth::user(), 'Contrato de compraventa firmado (confirmación manual).');
        }

        return back()->with('success', 'Contrato marcado como firmado. Proceso avanzó a Entrega.');
    }
}
