<?php

namespace App\Http\Controllers;

use App\Mail\ContractVersionMail;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\ContractVersion;
use App\Models\Operation;
use App\Models\RentalProcess;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function __construct(protected ContractService $contractService) {}

    /**
     * Listado global de todos los contratos (venta + renta).
     */
    public function index(Request $request)
    {
        $query = Contract::with([
            'operation.client', 'operation.secondaryClient', 'operation.property',
            'rentalProcess.ownerClient', 'rentalProcess.tenantClient', 'rentalProcess.property',
            'template', 'currentVersion',
        ]);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('estado')) {
            $estado = $request->estado;
            $query->where(function ($q) use ($estado) {
                $q->whereHas('currentVersion', fn ($v) => $v->where('signature_status', $estado))
                    ->orWhere(fn ($q2) => $q2->whereNull('current_version_id')->where('signature_status', $estado));
            });
        }

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('folio', 'like', $term)
                    ->orWhereHas('operation.client', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('operation.secondaryClient', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('operation.property', fn ($p) => $p->where('address', 'like', $term)->orWhere('title', 'like', $term))
                    ->orWhereHas('rentalProcess.ownerClient', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('rentalProcess.tenantClient', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('rentalProcess.property', fn ($p) => $p->where('address', 'like', $term)->orWhere('title', 'like', $term));
            });
        }

        $contracts = $query->latest()->paginate(20)->appends($request->only(['type', 'estado', 'q']));

        return view('contracts.index', compact('contracts'));
    }

    /**
     * Formulario para elegir un trato (Operación o Renta) y arrancar un contrato desde ahí.
     */
    public function create()
    {
        $operations = Operation::with(['property', 'client'])
            ->where('status', 'active')
            ->where('type', 'venta')
            ->latest()
            ->get();

        $rentals = RentalProcess::with(['property', 'ownerClient'])
            ->where('status', 'active')
            ->latest()
            ->get();

        $contractTemplates = ContractTemplate::active()->get();

        return view('contracts.create', compact('operations', 'rentals', 'contractTemplates'));
    }

    /**
     * Recibe el picker del índice global y reenvía a los métodos ya
     * existentes (generateForOperation/generate) sin duplicar su lógica.
     */
    public function createFromPicker(Request $request)
    {
        $request->validate(['deal' => 'required|string']);

        [$dealType, $dealId] = explode(':', $request->input('deal'), 2);

        return $dealType === 'operation'
            ? $this->generateForOperation($request, $dealId)
            : $this->generate($request, $dealId);
    }

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

        return redirect()->route('rentals.show', $rental->id)->with('success', 'Contrato generado exitosamente.');
    }

    /**
     * Upload an external contract file.
     */
    public function upload(Request $request, string $rentalId)
    {
        $rental = RentalProcess::findOrFail($rentalId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:rental,commission,renewal,sale',
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
     * Generate (or version) a contract from a template for an operation.
     *
     * Si ya existe un Contract vivo del mismo template para esta Operación,
     * no se crea otro — se genera una nueva versión sobre el existente.
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

        $contract = Contract::where('operation_id', $operation->id)
            ->where('contract_template_id', $template->id)
            ->first();

        if ($template->uses_clauses) {
            if (!$contract) {
                $contract = Contract::create([
                    'operation_id' => $operation->id,
                    'contract_template_id' => $template->id,
                    'type' => $template->type,
                    'title' => $validated['title'],
                    'source' => 'generated',
                    'notes' => $validated['notes'] ?? null,
                ]);
                $template->cloneClausesInto($contract);
            }

            $this->contractService->generateVersion($contract);

            return redirect()->route('operations.show', $operation->id)->with('success', 'Contrato generado — versión ' . $contract->fresh()->currentVersion->version_number . '.');
        }

        // Camino legacy (renta con plantilla de texto libre, sin cláusulas estructuradas).
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

        return redirect()->route('operations.show', $operation->id)->with('success', 'Contrato generado exitosamente.');
    }

    /**
     * Genera una nueva versión de un Contract de venta ya existente.
     */
    public function generateVersionForOperation(Request $request, string $operationId, string $contractId)
    {
        $contract = Contract::where('operation_id', $operationId)->findOrFail($contractId);

        $validated = $request->validate(['note' => 'nullable|string|max:500']);

        $version = $this->contractService->generateVersion($contract, $validated['note'] ?? null);

        return back()->with('success', 'Versión ' . $version->version_number . ' generada.');
    }

    /**
     * Genera una nueva versión de un Contract de renta con cláusulas estructuradas.
     */
    public function generateVersionForRental(Request $request, string $rentalId, string $contractId)
    {
        $contract = Contract::where('rental_process_id', $rentalId)->findOrFail($contractId);

        $validated = $request->validate(['note' => 'nullable|string|max:500']);

        $version = $this->contractService->generateVersion($contract, $validated['note'] ?? null);

        return back()->with('success', 'Versión ' . $version->version_number . ' generada.');
    }

    /**
     * Upload an external contract for an operation.
     */
    public function uploadForOperation(Request $request, string $operationId)
    {
        $operation = Operation::findOrFail($operationId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:rental,commission,renewal,sale',
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
        $contract = Contract::with('currentVersion')->findOrFail($contractId);

        $html = $contract->currentVersion->generated_html ?? $contract->generated_html;

        if (!$html) {
            return back()->with('error', 'Este contrato no tiene vista previa HTML.');
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Download contract PDF.
     */
    public function download(string $contractId)
    {
        $contract = Contract::with('currentVersion')->findOrFail($contractId);

        $pdfPath = $contract->currentVersion->pdf_path ?? $contract->pdf_path;

        if ($pdfPath && Storage::disk('public')->exists($pdfPath)) {
            $filename = str_replace(' ', '_', $contract->title) . '.pdf';
            return Storage::disk('public')->download($pdfPath, $filename);
        }

        // Camino legacy: generar PDF con Dompdf si hay HTML pero no archivo.
        if ($contract->generated_html) {
            $this->contractService->generatePdf($contract);
            $contract->refresh();
        }

        if (!$contract->pdf_path || !Storage::disk('public')->exists($contract->pdf_path)) {
            return back()->with('error', 'Archivo no encontrado.');
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
     * Preview HTML of a specific version.
     */
    public function previewVersion(string $contractId, string $versionId)
    {
        $version = ContractVersion::where('contract_id', $contractId)->findOrFail($versionId);

        if (!$version->generated_html) {
            return back()->with('error', 'Esta versión no tiene vista previa HTML.');
        }

        return response($version->generated_html)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Download a specific version's PDF.
     */
    public function downloadVersion(string $contractId, string $versionId)
    {
        $version = ContractVersion::with('contract')->where('contract_id', $contractId)->findOrFail($versionId);

        if (!$version->pdf_path || !Storage::disk('public')->exists($version->pdf_path)) {
            return back()->with('error', 'Archivo no encontrado.');
        }

        $filename = str_replace(' ', '_', $version->contract->title) . '-v' . $version->version_number . '.pdf';
        return Storage::disk('public')->download($version->pdf_path, $filename);
    }

    /**
     * Send a specific version's PDF by email to the counterparty.
     */
    public function sendVersion(Request $request, string $contractId, string $versionId)
    {
        $version = ContractVersion::with('contract')->where('contract_id', $contractId)->findOrFail($versionId);

        $validated = $request->validate(['to_email' => 'required|email']);

        if (!$version->pdf_path || !Storage::disk('public')->exists($version->pdf_path)) {
            return back()->with('error', 'Esta versión no tiene PDF generado.');
        }

        Mail::to($validated['to_email'])->send(new ContractVersionMail($version));
        $version->update(['sent_at' => now()]);

        return back()->with('success', 'Versión ' . $version->version_number . ' enviada a ' . $validated['to_email'] . '.');
    }

    /**
     * Record digital confirmation signature on a specific version.
     */
    public function signVersion(Request $request, string $contractId, string $versionId)
    {
        $version = ContractVersion::where('contract_id', $contractId)->findOrFail($versionId);

        if ($version->is_signed) {
            return back()->with('error', 'Esta versión ya está firmada.');
        }

        $validated = $request->validate([
            'signer_name' => 'required|string|max:255',
            'signer_email' => 'required|email|max:255',
        ]);

        $this->contractService->recordVersionSignature(
            $version,
            Auth::id(),
            $validated['signer_name'],
            $validated['signer_email'],
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Firma digital registrada exitosamente.');
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
