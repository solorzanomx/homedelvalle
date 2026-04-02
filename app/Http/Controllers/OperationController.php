<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\OperationChecklistItem;
use App\Models\OperationStageLog;
use App\Models\ContractTemplate;
use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\Document;
use App\Models\User;
use App\Models\Notification;
use App\Services\OperationChecklistService;
use App\Helpers\MentionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    public function __construct(protected OperationChecklistService $checklistService) {}

    public function index(Request $request)
    {
        $query = Operation::with(['property', 'client', 'broker', 'user']);

        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        if ($request->filled('stage')) {
            $query->byStage($request->stage);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->whereHas('client', fn($q2) => $q2->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('property', fn($q2) => $q2->where('title', 'like', "%{$s}%"));
            });
        }

        // Kanban data
        $typeFilter = $request->input('type', 'all');
        $stages = match($typeFilter) {
            'venta' => Operation::VENTA_STAGES,
            'renta' => Operation::RENTA_STAGES,
            'captacion' => Operation::CAPTACION_STAGES,
            default => array_keys(Operation::STAGES),
        };

        $operationsByStage = [];
        foreach ($stages as $stageKey) {
            $stageQuery = Operation::active()->byStage($stageKey)->with(['client', 'property', 'user', 'checklistItems']);
            if ($typeFilter !== 'all') {
                $stageQuery->byType($typeFilter);
            }
            $operationsByStage[$stageKey] = $stageQuery->latest()->take(20)->get();
        }

        // Stats
        $statsQuery = Operation::query();
        if ($typeFilter !== 'all') $statsQuery->byType($typeFilter);
        $stats = [
            'total' => (clone $statsQuery)->active()->count(),
            'ventas' => Operation::active()->ventas()->count(),
            'rentas' => Operation::active()->rentas()->count(),
            'captaciones' => Operation::active()->captaciones()->count(),
            'pipeline_value' => Operation::active()->sum('amount') + Operation::active()->sum('monthly_rent'),
        ];

        $operations = $query->latest()->paginate(20);
        $users = \App\Models\User::whereIn('role', ['admin', 'broker', 'user'])->orderBy('name')->get();

        return view('operations.index', compact('operations', 'operationsByStage', 'stats', 'stages', 'users'));
    }

    public function create()
    {
        $properties = Property::orderBy('title')->get();
        $clients = Client::orderBy('name')->get();
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();

        return view('operations.create', compact('properties', 'clients', 'brokers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:venta,renta,captacion',
            'target_type' => 'nullable|required_if:type,captacion|in:venta,renta',
            'property_id' => 'nullable|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'secondary_client_id' => 'nullable|exists:clients,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'amount' => 'nullable|numeric|min:0',
            'monthly_rent' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'deposit_amount' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'guarantee_type' => 'nullable|in:deposito,poliza_juridica,fianza',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['stage'] = 'lead';
        $validated['phase'] = 'captacion';

        $operation = Operation::create($validated);

        // Initialize checklist and create first stage log
        $this->checklistService->initializeChecklistForStage($operation, 'lead');

        OperationStageLog::create([
            'operation_id' => $operation->id,
            'user_id' => Auth::id(),
            'to_stage' => 'lead',
            'to_phase' => 'captacion',
            'notes' => 'Operacion creada',
        ]);

        return redirect()->route('operations.show', $operation)->with('success', 'Operacion creada exitosamente.');
    }

    public function show(string $id)
    {
        $operation = Operation::with([
            'property', 'client', 'secondaryClient', 'broker', 'user',
            'stageLogs.user',
            'checklistItems.template', 'checklistItems.completedByUser',
            'documents.uploader', 'tasks.user',
            'contracts.template', 'contracts.signer',
            'poliza.events.user', 'commissions',
            'sourceOperation', 'spawnedOperations',
            'comments.user',
        ])->findOrFail($id);

        // Build timeline
        $timeline = collect();

        foreach ($operation->stageLogs as $log) {
            $timeline->push([
                'date' => $log->created_at,
                'type' => 'stage_change',
                'type_label' => 'Cambio de Etapa',
                'color' => '#8b5cf6',
                'body' => e(($log->from_stage ? Operation::STAGES[$log->from_stage] ?? $log->from_stage : 'Inicio') . ' → ' . (Operation::STAGES[$log->to_stage] ?? $log->to_stage)),
                'meta' => e($log->user->name ?? '') . ($log->notes ? ' — ' . e($log->notes) : ''),
            ]);
        }

        foreach ($operation->documents as $doc) {
            $timeline->push([
                'date' => $doc->created_at,
                'type' => 'document',
                'type_label' => 'Documento',
                'color' => '#3b82f6',
                'body' => e($doc->label) . ' <span class="badge badge-' . match($doc->status) { 'verified' => 'green', 'rejected' => 'red', 'received' => 'blue', default => 'yellow' } . '">' . e($doc->status_label) . '</span>',
                'meta' => e($doc->uploader->name ?? '') . ' — ' . e($doc->category_label),
            ]);
        }

        foreach ($operation->tasks as $task) {
            $timeline->push([
                'date' => $task->created_at,
                'type' => 'task',
                'type_label' => 'Tarea',
                'color' => '#f59e0b',
                'body' => e($task->title) . ' <span class="badge badge-' . match($task->status) { 'completed' => 'green', 'in_progress' => 'blue', default => 'yellow' } . '">' . e(match($task->status) { 'completed' => 'Completada', 'in_progress' => 'En progreso', default => 'Pendiente' }) . '</span>',
                'meta' => e($task->user->name ?? ''),
            ]);
        }

        foreach ($operation->comments as $comment) {
            $timeline->push([
                'date' => $comment->created_at,
                'type' => 'comment',
                'type_label' => 'Comentario',
                'color' => '#10b981',
                'body' => MentionHelper::render($comment->body),
                'meta' => e($comment->user->name ?? ''),
            ]);
        }

        $timeline = $timeline->sortByDesc('date')->values();

        $progress = $this->checklistService->getStageProgress($operation);
        $documentCategories = Document::CATEGORIES;
        $contractTemplates = ContractTemplate::active()->get();

        return view('operations.show', compact('operation', 'timeline', 'progress', 'documentCategories', 'contractTemplates'));
    }

    public function edit(string $id)
    {
        $operation = Operation::with(['property', 'client', 'secondaryClient'])->findOrFail($id);
        $properties = Property::orderBy('title')->get();
        $clients = Client::orderBy('name')->get();
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();

        return view('operations.edit', compact('operation', 'properties', 'clients', 'brokers'));
    }

    public function update(Request $request, string $id)
    {
        $operation = Operation::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:venta,renta,captacion',
            'target_type' => 'nullable|required_if:type,captacion|in:venta,renta',
            'property_id' => 'nullable|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'secondary_client_id' => 'nullable|exists:clients,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'amount' => 'nullable|numeric|min:0',
            'monthly_rent' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:MXN,USD',
            'deposit_amount' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'guarantee_type' => 'nullable|in:deposito,poliza_juridica,fianza',
            'expected_close_date' => 'nullable|date',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after_or_equal:lease_start_date',
            'lease_duration_months' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:2000',
        ]);

        $operation->update($validated);

        return redirect()->route('operations.show', $operation)->with('success', 'Operacion actualizada.');
    }

    public function updateStage(Request $request, string $id)
    {
        $operation = Operation::findOrFail($id);

        $validated = $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys($operation->getAvailableStages())),
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['stage'] === $operation->stage) {
            return back()->with('error', 'La operacion ya esta en esa etapa.');
        }

        $this->checklistService->changeStage($operation, $validated['stage'], Auth::user(), $validated['notes'] ?? null);

        return back()->with('success', 'Etapa actualizada a: ' . (Operation::STAGES[$validated['stage']] ?? $validated['stage']));
    }

    public function toggleChecklist(Request $request, string $operationId, string $itemId)
    {
        $operation = Operation::findOrFail($operationId);
        $item = OperationChecklistItem::where('operation_id', $operation->id)->findOrFail($itemId);

        $autoAdvanced = $this->checklistService->toggleChecklistItem($item, Auth::user());

        if ($autoAdvanced) {
            $operation->refresh();

            // If captacion completed and spawned a new operation, redirect to it
            if ($operation->status === 'completed' && $operation->type === 'captacion') {
                $spawned = Operation::where('source_operation_id', $operation->id)->latest()->first();
                if ($spawned) {
                    return redirect()->route('operations.show', $spawned)
                        ->with('success', 'Captacion completada! Se creo operacion de ' . $spawned->type_label . ' #' . $spawned->id);
                }
            }

            return back()->with('success', 'Checklist completado! Avanzado a: ' . $operation->stage_label);
        }

        return back();
    }

    public function destroy(string $id)
    {
        Operation::findOrFail($id)->delete();
        return redirect()->route('operations.index')->with('success', 'Operacion eliminada.');
    }

    public function storeComment(Request $request, string $id)
    {
        $operation = Operation::findOrFail($id);
        $validated = $request->validate(['body' => 'required|string|max:2000']);
        $comment = $operation->comments()->create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
        ]);
        $this->processMentions($validated['body'], $operation);
        return redirect()->route('operations.show', $operation)->with('success', 'Comentario agregado.');
    }

    private function processMentions(string $text, Operation $operation): void
    {
        preg_match_all('/@([A-Za-zÀ-ÿ]+(?:\s+[A-Za-zÀ-ÿ]+)?)/', $text, $matches);

        if (empty($matches[1])) {
            return;
        }

        $currentUserId = Auth::id();
        $currentUser = Auth::user();
        $notified = [];

        foreach ($matches[1] as $mention) {
            $mention = trim($mention);
            $parts = preg_split('/\s+/', $mention, 2);

            $query = User::where('name', 'LIKE', $parts[0] . '%');
            if (isset($parts[1])) {
                $query->where('last_name', 'LIKE', $parts[1] . '%');
            }

            $users = $query->where('id', '!=', $currentUserId)->get();

            foreach ($users as $user) {
                if (in_array($user->id, $notified)) {
                    continue;
                }

                Notification::create([
                    'user_id' => $user->id,
                    'from_user_id' => $currentUserId,
                    'type' => 'mention',
                    'title' => $currentUser->full_name . ' te menciono en Operacion #' . $operation->id,
                    'body' => $text,
                    'data' => [
                        'operation_id' => $operation->id,
                        'url' => route('operations.show', $operation),
                    ],
                ]);

                $notified[] = $user->id;
            }
        }
    }
}
