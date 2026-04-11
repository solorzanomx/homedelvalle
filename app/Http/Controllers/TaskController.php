<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Deal;
use App\Models\Client;
use App\Models\Property;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\LeadScoringService;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['user', 'deal', 'client', 'property', 'operation']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('operation_id')) {
            $query->where('operation_id', $request->operation_id);
        }

        $tasks = $query->latest()->paginate(20)->appends($request->only(['status', 'priority']));

        $stats = [
            'total' => Task::count(),
            'pending' => Task::where('status', 'pending')->count(),
            'overdue' => Task::where('status', '!=', 'completed')
                ->where('status', '!=', 'cancelled')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->count(),
            'completed_this_week' => Task::where('status', 'completed')
                ->where('completed_at', '>=', now()->startOfWeek())
                ->count(),
        ];

        return view('tasks.index', compact('tasks', 'stats'));
    }

    public function create(Request $request)
    {
        $deals = Deal::all();
        $clients = Client::all();
        $properties = Property::all();
        $operations = Operation::with(['property', 'client'])->where('status', 'active')->latest()->get();
        $preselectedOperationId = $request->get('operation_id');

        return view('tasks.create', compact('deals', 'clients', 'properties', 'operations', 'preselectedOperationId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
            'deal_id' => 'nullable|exists:deals,id',
            'client_id' => 'nullable|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
            'operation_id' => 'nullable|exists:operations,id',
        ]);

        $validated['user_id'] = auth()->id();

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $task = Task::create($validated);

        if ($task->operation_id) {
            return redirect()->route('operations.show', $task->operation_id)->with('success', 'Tarea creada exitosamente.');
        }

        return redirect()->route('tasks.index')->with('success', 'Tarea creada exitosamente.');
    }

    public function show(string $id)
    {
        return redirect()->route('tasks.edit', $id);
    }

    public function edit(string $id)
    {
        $task = Task::with(['user', 'deal', 'client', 'property', 'operation'])->findOrFail($id);
        $deals = Deal::all();
        $clients = Client::all();
        $properties = Property::all();
        $operations = Operation::with(['property', 'client'])->where('status', 'active')->latest()->get();

        return view('tasks.edit', compact('task', 'deals', 'clients', 'properties', 'operations'));
    }

    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
            'deal_id' => 'nullable|exists:deals,id',
            'client_id' => 'nullable|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
            'operation_id' => 'nullable|exists:operations,id',
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $validated['completed_at'] = now();
        } elseif ($newStatus !== 'completed' && $oldStatus === 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        // Score task completion
        if ($newStatus === 'completed' && $oldStatus !== 'completed' && $task->client_id) {
            app(LeadScoringService::class)->processEvent($task->client_id, 'task_completed', ['source' => 'task_update']);
        }

        return redirect()->route('tasks.edit', $task)->with('success', 'Tarea actualizada exitosamente.');
    }

    public function toggleComplete(string $id)
    {
        $task = Task::findOrFail($id);

        if ($task->status === 'completed') {
            $task->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
        } else {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Score task completion
            if ($task->client_id) {
                app(LeadScoringService::class)->processEvent($task->client_id, 'task_completed', ['source' => 'task_toggle']);
            }
        }

        return redirect()->back()->with('success', 'Estado de tarea actualizado.');
    }

    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Tarea eliminada exitosamente.');
    }
}
