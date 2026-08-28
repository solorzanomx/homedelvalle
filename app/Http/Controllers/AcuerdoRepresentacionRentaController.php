<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Operation;
use App\Services\AcuerdoRepresentacionRentaGeneratorService;
use App\Services\OperationChecklistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class AcuerdoRepresentacionRentaController extends Controller
{
    public function generar(Request $request, Operation $operation, AcuerdoRepresentacionRentaGeneratorService $generator)
    {
        if ($operation->type !== 'renta') {
            return back()->with('error', 'Esta Operation no es de tipo renta.');
        }

        $missing = $generator::missingOwnershipFields($operation->property);
        if ($missing) {
            return back()->with('error', 'Completa los datos de escritura del inmueble antes de generar el Acuerdo: ' . implode(', ', $missing) . '.');
        }

        $validated = $request->validate(['vigencia_dias' => 'nullable|integer|min:30|max:180']);
        $vigenciaDias = $validated['vigencia_dias'] ?? 90;

        try {
            $path = $generator->generatePdf($operation, $vigenciaDias);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar el Acuerdo: ' . $e->getMessage());
        }

        Document::create([
            'operation_id' => $operation->id,
            'client_id'    => $operation->client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'contrato_exclusiva_renta',
            'label'        => 'Acuerdo de Representación (Renta) — ' . now()->format('d/m/Y'),
            'file_path'    => $path,
            'file_name'    => 'ARR-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return back()->with('success', 'Acuerdo de Representación (Renta) generado correctamente.');
    }

    public function pdf(Operation $operation)
    {
        $document = Document::where('operation_id', $operation->id)
            ->where('category', 'contrato_exclusiva_renta')
            ->latest()
            ->first();

        if (!$document || !file_exists($document->file_path)) {
            abort(404, 'PDF no encontrado.');
        }

        return Response::make(file_get_contents($document->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="acuerdo-representacion-renta.pdf"',
        ]);
    }

    public function markSigned(Operation $operation, OperationChecklistService $checklistService)
    {
        $document = Document::where('operation_id', $operation->id)
            ->where('category', 'contrato_exclusiva_renta')
            ->exists();

        if (!$document) {
            return back()->with('error', 'No hay Acuerdo de Representación (Renta) generado.');
        }

        if ($operation->stage === 'exclusiva') {
            $checklistService->changeStage($operation, 'mejoras', Auth::user(), 'Acuerdo de Representación (Renta) firmado (confirmación manual).');
        }

        return back()->with('success', 'Acuerdo marcado como firmado. Proceso avanzó a Mejoras.');
    }
}
