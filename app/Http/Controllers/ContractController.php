<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Operation;
use App\Models\RentalProcess;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function __construct(protected ContractService $contractService) {}

    /**
     * Generate a contract from a template.
     */
    public function generate(Request $request, string $rentalId)
    {
        $rental = RentalProcess::findOrFail($rentalId);

        $validated = $request->validate([
            'contract_template_id' => 'required|exists:contract_templates,id',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $template = ContractTemplate::findOrFail($validated['contract_template_id']);
        $html = $this->contractService->generateFromTemplate($template, $rental);

        $contract = Contract::create([
            'rental_process_id' => $rental->id,
            'contract_template_id' => $template->id,
            'type' => $template->type,
            'title' => $validated['title'],
            'generated_html' => $html,
            'source' => 'generated',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto-generate PDF
        $this->contractService->generatePdf($contract);

        return back()->with('success', 'Contrato generado exitosamente.');
    }

    /**
     * Upload an external contract file.
     */
    public function upload(Request $request, string $rentalId)
    {
        $rental = RentalProcess::findOrFail($rentalId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:rental,commission,renewal',
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $path = $file->store('contracts/rental-' . $rental->id, 'public');

        Contract::create([
            'rental_process_id' => $rental->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'pdf_path' => $path,
            'source' => 'uploaded',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Contrato subido exitosamente.');
    }

    /**
     * Generate a contract from a template for an operation.
     */
    public function generateForOperation(Request $request, string $operationId)
    {
        $operation = Operation::with(['property', 'client', 'secondaryClient', 'user'])->findOrFail($operationId);

        $validated = $request->validate([
            'contract_template_id' => 'required|exists:contract_templates,id',
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $template = ContractTemplate::findOrFail($validated['contract_template_id']);

        // Build variables from operation data
        $variables = [
            'fecha_actual' => now()->format('d/m/Y'),
            'nombre_propietario' => $operation->client->name ?? '',
            'nombre_inquilino' => $operation->secondaryClient->name ?? '',
            'direccion_propiedad' => $operation->property->address ?? '',
            'titulo_propiedad' => $operation->property->title ?? '',
            'renta_mensual' => number_format($operation->monthly_rent ?? 0, 2),
            'monto_deposito' => number_format($operation->deposit_amount ?? 0, 2),
            'monto_operacion' => number_format($operation->amount ?? 0, 2),
            'fecha_inicio' => $operation->lease_start_date ? $operation->lease_start_date->format('d/m/Y') : '',
            'fecha_fin' => $operation->lease_end_date ? $operation->lease_end_date->format('d/m/Y') : '',
            'duracion_meses' => $operation->lease_duration_months ?? '',
            'nombre_broker' => $operation->user->full_name ?? '',
            'moneda' => $operation->currency ?? 'MXN',
        ];

        $html = $template->body;
        foreach ($variables as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        $contract = Contract::create([
            'operation_id' => $operation->id,
            'contract_template_id' => $template->id,
            'type' => $template->type,
            'title' => $validated['title'],
            'generated_html' => $html,
            'source' => 'generated',
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->contractService->generatePdf($contract);

        return back()->with('success', 'Contrato generado exitosamente.');
    }

    /**
     * Upload an external contract for an operation.
     */
    public function uploadForOperation(Request $request, string $operationId)
    {
        $operation = Operation::findOrFail($operationId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:rental,commission,renewal',
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $path = $file->store('contracts/operation-' . $operation->id, 'public');

        Contract::create([
            'operation_id' => $operation->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'pdf_path' => $path,
            'source' => 'uploaded',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Contrato subido exitosamente.');
    }

    /**
     * Preview generated contract HTML.
     */
    public function preview(string $contractId)
    {
        $contract = Contract::findOrFail($contractId);

        if (!$contract->generated_html) {
            return back()->with('error', 'Este contrato no tiene vista previa HTML.');
        }

        return response($contract->generated_html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Download contract PDF.
     */
    public function download(string $contractId)
    {
        $contract = Contract::findOrFail($contractId);

        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            // Try to generate PDF if we have HTML
            if ($contract->generated_html) {
                $this->contractService->generatePdf($contract);
                $contract->refresh();
            }

            if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
                return back()->with('error', 'Archivo no encontrado.');
            }
        }

        $filename = str_replace(' ', '_', $contract->title) . '.pdf';
        return Storage::disk('public')->download($contract->pdf_path, $filename);
    }

    /**
     * Record digital confirmation signature.
     */
    public function sign(Request $request, string $contractId)
    {
        $contract = Contract::findOrFail($contractId);

        if ($contract->is_signed) {
            return back()->with('error', 'Este contrato ya esta firmado.');
        }

        $validated = $request->validate([
            'signer_name' => 'required|string|max:255',
            'signer_email' => 'required|email|max:255',
        ]);

        $this->contractService->recordDigitalSignature(
            $contract,
            Auth::id(),
            $validated['signer_name'],
            $validated['signer_email'],
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Firma digital registrada exitosamente.');
    }

    /**
     * Send contract for signature (mark as pending).
     */
    public function sendForSignature(string $contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $contract->update(['signature_status' => 'pending_signature']);
        return back()->with('success', 'Contrato marcado como pendiente de firma.');
    }

    /**
     * Delete a contract.
     */
    public function destroy(string $contractId)
    {
        $contract = Contract::findOrFail($contractId);

        if ($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path)) {
            Storage::disk('public')->delete($contract->pdf_path);
        }

        $contract->delete();

        return back()->with('success', 'Contrato eliminado.');
    }
}
