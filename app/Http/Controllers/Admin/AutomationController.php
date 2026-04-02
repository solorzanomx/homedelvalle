<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\AutomationLog;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index()
    {
        $rules = AutomationRule::withCount('logs')
            ->withCount(['logs as failed_logs_count' => function ($q) {
                $q->where('status', 'failed');
            }])
            ->orderByDesc('updated_at')
            ->get();

        $stats = [
            'total' => $rules->count(),
            'active' => $rules->where('is_active', true)->count(),
            'executions' => $rules->sum('trigger_count'),
            'failed' => AutomationLog::where('status', 'failed')->count(),
        ];

        return view('admin.automations.index', compact('rules', 'stats'));
    }

    public function create()
    {
        $triggers = $this->triggerOptions();
        $actions = $this->actionOptions();

        return view('admin.automations.create', compact('triggers', 'actions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger' => 'required|in:new_client,new_property,deal_stage_change,property_days_listed,client_inactive,task_overdue',
            'conditions' => 'nullable|string',
            'action' => 'required|in:send_email,create_task,notify_user,change_status',
            'action_config' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Decode JSON strings
        $validated['conditions'] = $this->decodeJson($validated['conditions'] ?? null);
        $validated['action_config'] = $this->decodeJson($validated['action_config'] ?? null);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['trigger_count'] = 0;

        AutomationRule::create($validated);

        return redirect()->route('admin.automations.index')->with('success', 'Regla de automatizacion creada correctamente');
    }

    public function edit(AutomationRule $automation)
    {
        $triggers = $this->triggerOptions();
        $actions = $this->actionOptions();
        $recentLogs = $automation->logs()->orderByDesc('created_at')->limit(10)->get();

        return view('admin.automations.edit', compact('automation', 'triggers', 'actions', 'recentLogs'));
    }

    public function update(Request $request, AutomationRule $automation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger' => 'required|in:new_client,new_property,deal_stage_change,property_days_listed,client_inactive,task_overdue',
            'conditions' => 'nullable|string',
            'action' => 'required|in:send_email,create_task,notify_user,change_status',
            'action_config' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['conditions'] = $this->decodeJson($validated['conditions'] ?? null);
        $validated['action_config'] = $this->decodeJson($validated['action_config'] ?? null);
        $validated['is_active'] = $request->boolean('is_active');

        $automation->update($validated);

        return redirect()->route('admin.automations.index')->with('success', 'Regla de automatizacion actualizada');
    }

    public function toggleActive(AutomationRule $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);

        $state = $automation->is_active ? 'activada' : 'desactivada';
        return back()->with('success', "Regla \"{$automation->name}\" {$state}");
    }

    public function logs(Request $request)
    {
        $query = AutomationLog::with('rule')->orderByDesc('created_at');

        if ($request->filled('rule_id')) {
            $query->where('automation_rule_id', $request->rule_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->paginate(30)->withQueryString();
        $rules = AutomationRule::orderBy('name')->get();

        return view('admin.automations.logs', compact('logs', 'rules'));
    }

    public function destroy(AutomationRule $automation)
    {
        $automation->logs()->delete();
        $automation->delete();

        return redirect()->route('admin.automations.index')->with('success', 'Regla eliminada correctamente');
    }

    // ─── Helpers ──────────────────────────────────────────

    protected function triggerOptions(): array
    {
        return [
            'new_client' => 'Nuevo Cliente',
            'new_property' => 'Nueva Propiedad',
            'deal_stage_change' => 'Cambio Etapa Deal',
            'property_days_listed' => 'Dias en Listado',
            'client_inactive' => 'Cliente Inactivo',
            'task_overdue' => 'Tarea Vencida',
        ];
    }

    protected function actionOptions(): array
    {
        return [
            'send_email' => 'Enviar Email',
            'create_task' => 'Crear Tarea',
            'notify_user' => 'Notificar Usuario',
            'change_status' => 'Cambiar Estado',
        ];
    }

    protected function decodeJson(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
