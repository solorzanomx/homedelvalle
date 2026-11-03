<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\RentalProcess;
use App\Services\PagaresGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class PagaresController extends Controller
{
    public function generar(RentalProcess $rental, PagaresGeneratorService $generator)
    {
        if (! $rental->tenantClient || ! $rental->ownerClient) {
            return back()->with('error', 'Falta vincular propietario e inquilino a esta Renta para generar los Pagarés.');
        }
        if (! $rental->lease_duration_months || ! $rental->monthly_rent) {
            return back()->with('error', 'Falta la duración (meses) o la renta mensual del trato para calcular la serie de Pagarés.');
        }

        try {
            $path = $generator->generatePdf($rental);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar los Pagarés: ' . $e->getMessage());
        }

        $total = (int) $rental->lease_duration_months;

        Document::create([
            'rental_process_id' => $rental->id,
            'client_id'    => $rental->tenant_client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'pagare',
            'label'        => 'Serie de Pagarés (' . $total . ') — $' . number_format((float) $rental->monthly_rent, 2) . '/mes',
            'file_path'    => $path,
            'file_name'    => 'PAG-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT) . '-' . now()->format('YmdHis') . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return back()->with('success', 'Pagarés generados correctamente (' . $total . ' documentos, uno por mes).');
    }

    public function pdf(RentalProcess $rental, Document $document)
    {
        if ($document->rental_process_id !== $rental->id || $document->category !== 'pagare') {
            abort(404);
        }
        if (!file_exists($document->file_path)) {
            abort(404, 'PDF no encontrado.');
        }

        return Response::make(file_get_contents($document->file_path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pagares.pdf"',
        ]);
    }
}
