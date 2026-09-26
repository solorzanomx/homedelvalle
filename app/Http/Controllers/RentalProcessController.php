<?php

namespace App\Http\Controllers;

use App\Models\RentalProcess;
use App\Models\RentalStageLog;
use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\Notification;
use App\Models\LeadEvent;
use App\Services\AutomationEngine;
use App\Services\LeadScoringService;
use App\Models\ContractTemplate;
use App\Models\TenantInvestigation;
use App\Helpers\MentionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class RentalProcessController extends Controller
{
    public function index(Request $request)
    {
        $query = RentalProcess::with(['property', 'ownerClient', 'tenantClient', 'broker'])->active();

        if ($request->filled('broker_id')) {
            $query->where('broker_id', $request->broker_id);
        }
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('property', fn($q) => $q->where('title', 'LIKE', "%{$s}%"))
                  ->orWhereHas('ownerClient', fn($q) => $q->where('name', 'LIKE', "%{$s}%"));
        }

        $rentals = $query->latest()->paginate(20);
        $stages = RentalProcess::STAGES;

        $rentalsByStage = [];
        foreach (array_keys($stages) as $stage) {
            $rentalsByStage[$stage] = RentalProcess::with(['property', 'ownerClient', 'tenantClient', 'broker'])
                ->active()->where('stage', $stage)->latest()->get();
        }

        $stats = [
            'total' => RentalProcess::active()->count(),
            'activo' => RentalProcess::active()->where('stage', 'activo')->count(),
            'valor_mensual' => RentalProcess::active()->whereNotIn('stage', ['cerrado', 'renovacion'])->sum('monthly_rent'),
            'por_vencer' => RentalProcess::expiringSoon(30)->count(),
        ];

        $brokers = Broker::where('status', 'active')->get();

        return view('rentals.index', compact('rentals', 'stages', 'rentalsByStage', 'stats', 'brokers'));
    }

    public function create(Request $request)
    {
        $properties = Property::whereIn('operation_type', ['rental', 'temporary_rental'])->get();
        $clients = Client::orderBy('name')->get();
        $brokers = Broker::where('status', 'active')->get();

        $prefill = [
            'property_id'      => $request->query('property'),
            'owner_client_id'  => $request->query('owner'),
            'tenant_client_id' => $request->query('tenant'),
        ];

        return view('rentals.create', compact('properties', 'clients', 'brokers', 'prefill'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id'                => 'required|exists:properties,id',
            'owner_client_id'            => 'nullable|exists:clients,id',
            'tenant_client_id'           => 'nullable|exists:clients,id',
            'broker_id'                  => 'nullable|exists:brokers,id',
            'monthly_rent'               => 'nullable|numeric|min:0',
            'currency'                   => 'nullable|in:MXN,USD',
            'deposit_amount'             => 'nullable|numeric|min:0',
            'commission_amount'          => 'nullable|numeric|min:0',
            'commission_percentage'      => 'nullable|numeric|min:0|max:100',
            'broker_commission_amount'   => 'nullable|numeric|min:0',
            'guarantee_type'             => ['nullable', Rule::in(array_keys(RentalProcess::GUARANTEE_TYPES))],
            'lease_start_date'           => 'nullable|date',
            'lease_end_date'             => 'nullable|date|after_or_equal:lease_start_date',
            'lease_duration_months'      => 'nullable|integer|min:1',
            'payment_frequency'          => 'nullable|in:mensual,trimestral,semestral,anual',
            'payment_day'                => 'nullable|integer|min:1|max:28',
            'annual_increase_type'       => 'nullable|in:none,inpc,fixed',
            'annual_increase_percentage' => 'nullable|numeric|min:0|max:100',
            'notes'                      => 'nullable|string|max:2000',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['stage'] = 'captacion';

        $rental = RentalProcess::create($validated);

        RentalStageLog::create([
            'rental_process_id' => $rental->id,
            'user_id' => Auth::id(),
            'from_stage' => null,
            'to_stage' => 'captacion',
            'notes' => 'Proceso de renta iniciado',
        ]);

        return redirect()->route('rentals.show', $rental)->with('success', 'Proceso de renta creado.');
    }

    public function show(string $id)
    {
        $rental = RentalProcess::with([
            'property', 'ownerClient', 'tenantClient', 'broker', 'user',
            'documents.uploader', 'documents.events.user', 'documents.client', 'tenantClient.references', 'stageLogs.user', 'tasks.user',
            'poliza.events.user', 'contracts.template', 'contracts.signer',
            'investigation.tenantClient',
        ])->findOrFail($id);

        // Build timeline from stage logs + documents + tasks
        $timeline = collect();

        foreach ($rental->stageLogs as $log) {
            $fromLabel = RentalProcess::STAGES[$log->from_stage] ?? null;
            $toLabel = RentalProcess::STAGES[$log->to_stage] ?? 'Inicio';
            $body = $fromLabel
                ? 'Cambio de <strong>' . e($fromLabel) . '</strong> a <strong>' . e($toLabel) . '</strong>'
                : '<strong>' . e($toLabel) . '</strong>';
            if ($log->notes) $body .= '<br><span style="color:var(--text-muted)">' . MentionHelper::render($log->notes) . '</span>';
            $timeline->push([
                'date' => $log->created_at,
                'dot' => 'stage',
                'color' => RentalProcess::STAGE_COLORS[$log->to_stage] ?? '#94a3b8',
                'type_label' => 'Etapa',
                'body' => $body,
                'meta' => 'Por ' . e($log->user->name ?? ''),
            ]);
        }

        foreach ($rental->documents as $doc) {
            $statusBadge = match($doc->status) {
                'verified' => '<span class="badge badge-green">Verificado</span>',
                'rejected' => '<span class="badge badge-red">Rechazado</span>',
                'received' => '<span class="badge badge-blue">Recibido</span>',
                default => '<span class="badge badge-yellow">Pendiente</span>',
            };
            $timeline->push([
                'date' => $doc->created_at,
                'dot' => 'document',
                'color' => '#6366f1',
                'type_label' => 'Documento',
                'body' => e($doc->label) . ' <span style="font-size:0.75rem;color:var(--text-muted);">(' . e($doc->category_label) . ')</span> ' . $statusBadge,
                'meta' => 'Subido por ' . e($doc->uploader->name ?? ''),
            ]);
        }

        foreach ($rental->tasks as $task) {
            $statusBadge = match($task->status) {
                'completed' => '<span class="badge badge-green">Completada</span>',
                'in_progress' => '<span class="badge badge-blue">En progreso</span>',
                'cancelled' => '<span class="badge badge-red">Cancelada</span>',
                default => '<span class="badge badge-yellow">Pendiente</span>',
            };
            $timeline->push([
                'date' => $task->created_at,
                'dot' => 'task',
                'color' => '#f59e0b',
                'type_label' => 'Tarea',
                'body' => e($task->title) . ' ' . $statusBadge,
                'meta' => 'Asignada a ' . e($task->user->name ?? ''),
            ]);
        }

        $timeline = $timeline->sortByDesc('date')->values();

        $documentCategories = \App\Models\Document::CATEGORIES;
        $documentChecklist = \App\Support\RentalDocumentChecklist::build($rental);

        $contractTemplates = ContractTemplate::active()->get();
        $providerCompanies = \App\Models\ProviderCompany::where('status', 'active')->with('contacts')->orderBy('name')->get();

        return view('rentals.show', compact('rental', 'timeline', 'documentCategories', 'documentChecklist', 'contractTemplates', 'providerCompanies'));
    }

    /** El asesor fija/corrige la ruta de garantía (el inquilino la declara en el Portal, pero el asesor tiene la última palabra). */
    public function setGuaranteeRoute(Request $request, string $id)
    {
        $rental = RentalProcess::findOrFail($id);
        $request->validate(['route' => 'required|in:poliza,aval']);

        if ($request->input('route') === 'poliza') {
            $rental->update(['tenant_has_aval' => false, 'guarantee_type' => 'poliza_juridica', 'guarantee_declared_at' => now()]);
            $msg = 'Ruta de garantía: póliza jurídica. El inquilino verá los planes en su Portal.';
        } else {
            $keep = in_array($rental->guarantee_type, ['aval', 'aval_pagares', 'pagares'], true) ? $rental->guarantee_type : 'aval';
            $rental->update(['tenant_has_aval' => true, 'guarantee_type' => $keep, 'guarantee_declared_at' => now()]);
            $msg = 'Ruta de garantía: aval con investigación ($' . number_format(\App\Support\TenantRoadmap::INVESTIGATION_FEE) . ').';
        }

        return back()->with('success', $msg);
    }

    /** El asesor decide la póliza A NOMBRE del propietario (p. ej. por WhatsApp/teléfono) o corrige la decisión. */
    public function setPolizaDecision(Request $request, string $id, \App\Services\PolizaDecisionService $decisions)
    {
        $rental = RentalProcess::with(['poliza', 'tenantClient'])->findOrFail($id);
        $data = $request->validate([
            'plan_id' => 'required|integer',
            'tenant_share' => 'required|in:' . implode(',', array_keys(\App\Support\PolizaPricing::SHARE_OPTIONS)),
        ]);
        $plan = \App\Models\PolizaPlan::offered()->find($data['plan_id']);
        if (! $plan) {
            return back()->with('error', 'Ese plan no está disponible.');
        }

        try {
            $r = $decisions->decide($rental, $plan, (int) $data['tenant_share'], 'advisor', Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Póliza {$plan->name} registrada: $" . number_format($r['amount']) . ' (inquilino ' . $r['split']['tenant_pct'] . '%).');
    }

    /** Forma de pago de la póliza (directo a Previsión Legal o vía Home del Valle) y marca de pagos por parte. */
    public function setPolizaPayment(Request $request, string $id)
    {
        $rental = RentalProcess::findOrFail($id);
        $request->validate(['payment_mode' => 'required|in:direct,hdv']);

        $rental->update([
            'poliza_payment_mode' => $request->input('payment_mode'),
            'poliza_tenant_paid_at' => $request->boolean('tenant_paid') ? ($rental->poliza_tenant_paid_at ?? now()) : null,
            'poliza_owner_paid_at' => $request->boolean('owner_paid') ? ($rental->poliza_owner_paid_at ?? now()) : null,
        ]);

        return back()->with('success', 'Forma de pago de la póliza actualizada.');
    }

    /** El asesor registra o cambia al obligado solidario (a petición del inquilino o por teléfono/WhatsApp). */
    public function registerObligado(Request $request, string $id, \App\Services\ObligadoSolidarioService $service)
    {
        $rental = RentalProcess::with(['tenantClient', 'ownerClient', 'obligado'])->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:150', 'email' => 'nullable|email|max:190', 'phone' => 'required|string|max:30', 'relationship' => 'nullable|string|max:80',
        ]);
        try {
            $os = $service->register($rental, $data, 'advisor');
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Obligado solidario registrado: {$os->name}. El inquilino captura sus datos y documentos desde su Portal (tú también puedes subirlos aquí).");
    }

    /** El asesor rechaza una referencia personal que no sirve (familiar directo, vive en la misma casa, no contesta…). El cliente debe dar otra. */
    public function rejectReference(Request $request, string $id, string $referenceId)
    {
        $rental = RentalProcess::with(['tenantClient', 'obligado'])->findOrFail($id);
        $ref = $this->rentalReference($rental, $referenceId);
        $data = $request->validate(['reason' => 'required|string|max:200']);

        $ref->update(['status' => 'rejected', 'rejection_reason' => trim($data['reason']), 'rejected_at' => now()]);

        // Avisa al inquilino (es quien captura, también las del obligado) para que dé otra persona.
        $tenantUserId = $rental->tenantClient?->user_id;
        if ($tenantUserId) {
            $whose = $ref->client_id === $rental->obligado_client_id ? ' de tu obligado solidario' : '';
            \App\Models\Notification::create([
                'user_id' => $tenantUserId,
                'type' => 'referencia_rechazada',
                'title' => 'Necesitamos otra referencia personal',
                'body' => "La referencia {$ref->name}{$whose} no nos sirve: {$ref->rejection_reason}. Entra a “Tus datos → Referencias personales” y captura a otra persona.",
                'data' => ['url' => route('portal.expediente', ['paso' => 'referencias'] + ($whose ? ['para' => 'obligado'] : []))],
            ]);
        }

        return back()->with('success', "Referencia rechazada. Se le pidió otra al inquilino.");
    }

    /** El asesor captura o corrige una referencia personal (p. ej. ayudando al cliente por teléfono). Reemplaza el hueco `slot`. */
    public function saveReference(Request $request, string $id)
    {
        $rental = RentalProcess::findOrFail($id);
        $data = $request->validate([
            'who' => 'required|in:tenant,obligado',
            'slot' => 'required|integer|min:1|max:3',
            'name' => 'required|string|max:150',
            'address' => 'nullable|string|max:200',
            'mobile_phone' => 'nullable|string|max:30',
            'landline_phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
        ]);
        $clientId = $data['who'] === 'tenant' ? $rental->tenant_client_id : $rental->obligado_client_id;
        abort_unless($clientId, 422, 'Esta renta no tiene esa persona registrada.');

        \App\Models\ClientReference::updateOrCreate(
            ['client_id' => $clientId, 'sort_order' => (int) $data['slot']],
            [
                'name' => trim($data['name']), 'address' => $data['address'] ?? null, 'mobile_phone' => $data['mobile_phone'] ?? null,
                'landline_phone' => $data['landline_phone'] ?? null, 'email' => $data['email'] ?? null,
                'status' => 'pending', 'rejection_reason' => null, 'rejected_at' => null,
            ]
        );

        return back()->with('success', 'Referencia guardada.');
    }

    /** Deshace el rechazo de una referencia. */
    public function restoreReference(string $id, string $referenceId)
    {
        $rental = RentalProcess::findOrFail($id);
        $this->rentalReference($rental, $referenceId)->update(['status' => 'pending', 'rejection_reason' => null, 'rejected_at' => null]);

        return back()->with('success', 'Referencia restaurada.');
    }

    /** Solo referencias del inquilino o del obligado de ESTA renta. */
    private function rentalReference(RentalProcess $rental, string $referenceId): \App\Models\ClientReference
    {
        $ids = array_filter([$rental->tenant_client_id, $rental->obligado_client_id]);

        return \App\Models\ClientReference::whereIn('client_id', $ids)->findOrFail($referenceId);
    }

    /** Exenta (o vuelve a requerir) al obligado solidario en este trato. */
    public function toggleObligado(Request $request, string $id)
    {
        $rental = RentalProcess::findOrFail($id);
        $request->validate(['required' => 'required|in:0,1']);
        $rental->update(['obligado_required' => $request->input('required') === '1' ? null : false]);

        return back()->with('success', $request->input('required') === '1' ? 'El obligado solidario vuelve a ser requisito de este trato.' : 'Obligado solidario exentado: ya no se pide en este trato.');
    }

    /** Vuelve a avisar al propietario (portal + correo) que le toca elegir la póliza. */
    public function remindOwnerPoliza(string $id, \App\Services\PolizaDecisionService $decisions)
    {
        $rental = RentalProcess::with('ownerClient')->findOrFail($id);
        $decisions->askOwnerToDecide($rental, true);

        return back()->with('success', 'Le avisamos de nuevo al propietario que debe elegir la póliza.');
    }

    public function edit(string $id)
    {
        $rental = RentalProcess::with(['property', 'ownerClient', 'tenantClient', 'broker'])->findOrFail($id);
        $properties = Property::whereIn('operation_type', ['rental', 'temporary_rental'])->get();
        $clients = Client::orderBy('name')->get();
        $brokers = Broker::where('status', 'active')->get();
        return view('rentals.edit', compact('rental', 'properties', 'clients', 'brokers'));
    }

    public function update(Request $request, string $id)
    {
        $rental = RentalProcess::findOrFail($id);

        $validated = $request->validate([
            'property_id'                => 'required|exists:properties,id',
            'owner_client_id'            => 'nullable|exists:clients,id',
            'tenant_client_id'           => 'nullable|exists:clients,id',
            'broker_id'                  => 'nullable|exists:brokers,id',
            'monthly_rent'               => 'nullable|numeric|min:0',
            'currency'                   => 'nullable|in:MXN,USD',
            'deposit_amount'             => 'nullable|numeric|min:0',
            'commission_amount'          => 'nullable|numeric|min:0',
            'commission_percentage'      => 'nullable|numeric|min:0|max:100',
            'broker_commission_amount'   => 'nullable|numeric|min:0',
            'guarantee_type'             => ['nullable', Rule::in(array_keys(RentalProcess::GUARANTEE_TYPES))],
            'lease_start_date'           => 'nullable|date',
            'lease_end_date'             => 'nullable|date|after_or_equal:lease_start_date',
            'lease_duration_months'      => 'nullable|integer|min:1',
            'payment_frequency'          => 'nullable|in:mensual,trimestral,semestral,anual',
            'payment_day'                => 'nullable|integer|min:1|max:28',
            'annual_increase_type'       => 'nullable|in:none,inpc,fixed',
            'annual_increase_percentage' => 'nullable|numeric|min:0|max:100',
            'notes'                      => 'nullable|string|max:2000',
        ]);

        $rental->update($validated);

        return redirect()->route('rentals.show', $rental)->with('success', 'Proceso actualizado.');
    }

    public function updateStage(Request $request, string $id)
    {
        $rental = RentalProcess::findOrFail($id);

        $validated = $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys(RentalProcess::STAGES)),
            'notes' => 'nullable|string|max:500',
        ]);

        $oldStage = $rental->stage;
        $newStage = $validated['stage'];

        if ($oldStage === $newStage) {
            return back();
        }

        $data = ['stage' => $newStage];
        if ($newStage === 'cerrado') {
            $data['status'] = 'completed';
            $data['completed_at'] = now();
        }

        $rental->update($data);

        RentalStageLog::create([
            'rental_process_id' => $rental->id,
            'user_id' => Auth::id(),
            'from_stage' => $oldStage,
            'to_stage' => $newStage,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Fire stage_change for both parties (owner + tenant)
        foreach (['owner_client_id', 'tenant_client_id'] as $field) {
            $clientId = $rental->{$field};
            if ($clientId) {
                $client = Client::find($clientId);
                if ($client) {
                    LeadEvent::record($clientId, 'stage_changed', [
                        'source' => 'rental',
                        'properties' => ['from_stage' => $oldStage, 'to_stage' => $newStage],
                    ]);
                    app(LeadScoringService::class)->processEvent($clientId, 'stage_changed', ['source' => 'rental']);
                    app(AutomationEngine::class)->processStageChange($client, $oldStage, $newStage, 'rental');
                }
            }
        }

        // Notify assigned broker
        if ($rental->broker_id) {
            $brokerName = $rental->broker->name ?? '';
            // Find user linked to this broker if you want, or notify all admins
        }

        return back()->with('success', 'Etapa actualizada a ' . RentalProcess::STAGES[$newStage]);
    }

    public function destroy(string $id)
    {
        RentalProcess::findOrFail($id)->delete();
        return redirect()->route('rentals.index')->with('success', 'Proceso eliminado.');
    }

    public function storeInvestigation(Request $request, string $id)
    {
        $rental = RentalProcess::with(['investigation', 'ownerClient.portalUser'])->findOrFail($id);

        $validated = $request->validate([
            'tenant_client_id'      => 'nullable|exists:clients,id',
            'occupation'            => 'nullable|string|max:150',
            'employer'              => 'nullable|string|max:150',
            'employment_years'      => 'nullable|integer|min:0|max:99',
            'income_type'           => 'nullable|in:employed,self_employed,business_owner,pension,other',
            'monthly_income'        => 'nullable|numeric|min:0',
            'income_verified'       => 'boolean',
            'credit_status'         => 'nullable|in:excellent,good,regular,poor',
            'bureau_checked'        => 'boolean',
            'credit_notes'          => 'nullable|string|max:500',
            'references_count'      => 'nullable|integer|min:0|max:10',
            'references_ok'         => 'boolean',
            'references_notes'      => 'nullable|string|max:500',
            'asesor_recommendation' => 'nullable|in:approve,conditional,decline',
            'asesor_notes'          => 'nullable|string|max:1000',
        ]);

        $validated['income_verified'] = $request->boolean('income_verified');
        $validated['bureau_checked']  = $request->boolean('bureau_checked');
        $validated['references_ok']   = $request->boolean('references_ok');
        $validated['created_by']      = Auth::id();

        if ($rental->investigation) {
            $rental->investigation->update($validated);
        } else {
            $validated['rental_process_id'] = $rental->id;
            TenantInvestigation::create($validated);
        }

        return back()->with('success', 'Investigación guardada.');
    }

    /**
     * Registra el apartado (pago de reserva previo a investigación/póliza)
     * y genera el recibo en PDF, adjuntándolo como Document del trato —
     * mismo patrón que OperationController::storePurchaseOffer() del lado
     * de venta.
     */
    public function storeApartado(Request $request, string $id, \App\Services\RentalDepositReceiptGeneratorService $generator)
    {
        $rental = RentalProcess::with('tenantClient', 'property', 'user')->findOrFail($id);

        $validated = $request->validate([
            'apartado_amount' => 'required|numeric|min:0',
            'apartado_paid_at' => 'required|date',
            'apartado_deadline' => 'required|date',
            'apartado_payment_method' => 'nullable|in:efectivo,transferencia,cheque',
            'apartado_notes' => 'nullable|string|max:1000',
        ]);

        $rental->update($validated);

        $path = $generator->generatePdf($rental->fresh(['tenantClient', 'property', 'user']));

        \App\Models\Document::create([
            'rental_process_id' => $rental->id,
            'client_id' => $rental->tenant_client_id,
            'uploaded_by' => Auth::id(),
            'category' => 'recibo_apartado',
            'label' => 'Recibo de Apartado — ' . now()->format('d/m/Y'),
            'file_path' => $path,
            'file_name' => 'RA-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => file_exists($path) ? filesize($path) : null,
        ]);

        return back()->with('success', 'Recibo de apartado generado.');
    }

    /**
     * Vista previa del recibo con los datos que trae el formulario en ese
     * momento — genera el PDF real (mismo Browsershot) pero SIN guardar
     * nada: no toca apartado_paid_at/amount/deadline en el rental, no crea
     * el Document. Permite revisar el recibo antes de confirmar el
     * apartado (que sí es la acción que queda en firme).
     */
    public function previewApartado(Request $request, string $id, \App\Services\RentalDepositReceiptGeneratorService $generator)
    {
        $rental = RentalProcess::with('tenantClient', 'ownerClient', 'property', 'user')->findOrFail($id);

        $validated = $request->validate([
            'apartado_amount' => 'required|numeric|min:0',
            'apartado_paid_at' => 'required|date',
            'apartado_deadline' => 'required|date',
            'apartado_payment_method' => 'nullable|in:efectivo,transferencia,cheque',
            'apartado_notes' => 'nullable|string|max:1000',
        ]);

        // Se asignan en memoria nada más — este modelo nunca se guarda.
        $rental->forceFill($validated);

        $path = $generator->generatePdf($rental);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="vista-previa-recibo-apartado.pdf"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Manda (o reenvía) el recibo de apartado ya generado por correo al
     * inquilino — no es automático al confirmar (decisión 2026-09-24): el
     * asesor decide cuándo mandarlo, como ya existe con resendInvitation()
     * del portal.
     */
    public function sendApartadoReceipt(string $id, \App\Services\EmailService $emailService)
    {
        $rental = RentalProcess::with('tenantClient', 'property')->findOrFail($id);

        $recibo = $rental->documents()->where('category', 'recibo_apartado')->latest()->first();
        if (! $recibo) {
            return back()->with('error', 'Este trato todavía no tiene un recibo de apartado generado.');
        }

        $tenant = $rental->tenantClient;
        if (! $tenant || ! $tenant->email) {
            return back()->with('error', 'El inquilino no tiene un correo registrado.');
        }

        $inmueble = $rental->property?->title ?? 'el inmueble';
        $subject = 'Recibo de tu apartado — ' . $inmueble;
        $body = '<p>Hola ' . e($tenant->name) . ',</p>'
            . '<p>Adjunto tu recibo de apartado para <strong>' . e($inmueble) . '</strong>. Puedes descargarlo también desde tu Portal en cualquier momento.</p>'
            . '<p>Saludos,<br>Home del Valle Bienes Raíces</p>';

        $sent = $emailService->send($tenant->email, $subject, $body, $tenant->name, null, Auth::user(), [$recibo->file_path]);

        return back()->with($sent ? 'success' : 'error', $sent
            ? 'Recibo enviado por correo a ' . $tenant->email . '.'
            : 'No se pudo enviar el correo — revisa la configuración de correo saliente.');
    }

    public function storeInvestigacionPago(Request $request, string $id, \App\Services\InvestigacionReceiptGeneratorService $generator)
    {
        $rental = RentalProcess::with('tenantClient', 'property')->findOrFail($id);

        $validated = $request->validate([
            'investigacion_amount' => 'required|numeric|min:0',
            'investigacion_paid_at' => 'required|date',
            'investigacion_payment_method' => 'nullable|in:efectivo,transferencia,cheque',
            'investigacion_notes' => 'nullable|string|max:1000',
        ]);

        $rental->update($validated);

        $path = $generator->generatePdf($rental->fresh(['tenantClient', 'property']));

        \App\Models\Document::create([
            'rental_process_id' => $rental->id,
            'client_id' => $rental->tenant_client_id,
            'uploaded_by' => Auth::id(),
            'category' => 'recibo_investigacion',
            'label' => 'Recibo de Cuota de Investigación — ' . now()->format('d/m/Y'),
            'file_path' => $path,
            'file_name' => 'RI-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => file_exists($path) ? filesize($path) : null,
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        return back()->with('success', 'Recibo de cuota de investigación generado.');
    }

    public function toggleInvestigation(Request $request, string $id)
    {
        $rental = RentalProcess::with(['investigation', 'ownerClient.portalUser'])->findOrFail($id);

        if (!$rental->investigation) {
            return back()->with('error', 'Completa la investigación antes de presentarla.');
        }

        $inv = $rental->investigation;
        $nowVisible = !$inv->visible_to_owner;

        $inv->update([
            'visible_to_owner' => $nowVisible,
            'presented_at'     => $nowVisible ? now() : null,
        ]);

        if ($nowVisible) {
            $rental->update(['proposed_tenant_at' => now()]);

            // Bell notification al propietario
            $ownerPortalUser = $rental->ownerClient?->portalUser;
            if ($ownerPortalUser) {
                Notification::create([
                    'user_id' => $ownerPortalUser->id,
                    'type'    => 'system',
                    'title'   => 'Tenemos un candidato para tu inmueble',
                    'body'    => 'Hemos completado la investigación. Ingresa al portal para revisar el perfil y dar tu respuesta.',
                    'data'    => ['url' => null],
                ]);

                // Email al propietario
                if ($ownerPortalUser->email) {
                    try {
                        Mail::to($ownerPortalUser->email)
                            ->send(new \App\Mail\Portal\TenantProposedOwnerMail($rental->load('investigation.tenantClient', 'property')));
                    } catch (\Exception $e) {
                        Log::warning('TenantProposedOwnerMail failed: ' . $e->getMessage());
                    }
                }
            }

            return back()->with('success', 'Candidato presentado al propietario. Se envió notificación.');
        }

        return back()->with('success', 'Candidato ocultado del portal.');
    }
}
