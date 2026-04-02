<?php

namespace App\Http\Controllers;

use App\Models\RentalProcess;
use App\Models\RentalStageLog;
use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\Notification;
use App\Models\ContractTemplate;
use App\Helpers\MentionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function create()
    {
        $properties = Property::whereIn('operation_type', ['rental', 'temporary_rental'])->get();
        $clients = Client::orderBy('name')->get();
        $brokers = Broker::where('status', 'active')->get();
        return view('rentals.create', compact('properties', 'clients', 'brokers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'owner_client_id' => 'nullable|exists:clients,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'monthly_rent' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'deposit_amount' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'guarantee_type' => 'nullable|in:deposito,poliza_juridica,fianza',
            'lease_duration_months' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:2000',
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
            'documents.uploader', 'stageLogs.user', 'tasks.user',
            'poliza.events.user', 'contracts.template', 'contracts.signer',
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

        $contractTemplates = ContractTemplate::active()->get();

        return view('rentals.show', compact('rental', 'timeline', 'documentCategories', 'contractTemplates'));
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
            'property_id' => 'required|exists:properties,id',
            'owner_client_id' => 'nullable|exists:clients,id',
            'tenant_client_id' => 'nullable|exists:clients,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'monthly_rent' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'deposit_amount' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'guarantee_type' => 'nullable|in:deposito,poliza_juridica,fianza',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after_or_equal:lease_start_date',
            'lease_duration_months' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:2000',
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
}
