<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\AutomationStep;
use App\Models\Client;
use App\Models\Segment;
use App\Services\AutomationEngine;
use Illuminate\Http\Request;

class AutomationEngineController extends Controller
{
    public function index()
    {
        $automations = Automation::withCount(['enrollments', 'steps'])
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.automations.engine-index', compact('automations'));
    }

    public function create()
    {
        $segments = Segment::active()->orderBy('name')->get();
        return view('admin.automations.engine-form', [
            'automation' => null,
            'segments' => $segments,
            'triggers' => Automation::TRIGGERS,
            'stepTypes' => Automation::STEP_TYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'required|string|in:' . implode(',', array_keys(Automation::TRIGGERS)),
            'trigger_config' => 'nullable|array',
            'is_active' => 'boolean',
            'allow_reentry' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.type' => 'required|string|in:' . implode(',', array_keys(Automation::STEP_TYPES)),
            'steps.*.config' => 'required|array',
        ]);

        $automation = Automation::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_type' => $validated['trigger_type'],
            'trigger_config' => $validated['trigger_config'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'allow_reentry' => $request->boolean('allow_reentry'),
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['steps'] as $i => $step) {
            AutomationStep::create([
                'automation_id' => $automation->id,
                'position' => $i,
                'type' => $step['type'],
                'config' => $step['config'],
            ]);
        }

        return redirect()->route('admin.automations-engine.index')->with('success', 'Automatizacion creada');
    }

    public function show(Automation $automation)
    {
        $automation->load(['steps', 'enrollments.client', 'enrollments.stepLogs']);

        return view('admin.automations.engine-show', compact('automation'));
    }

    public function edit(Automation $automation)
    {
        $automation->load('steps');
        $segments = Segment::active()->orderBy('name')->get();

        return view('admin.automations.engine-form', [
            'automation' => $automation,
            'segments' => $segments,
            'triggers' => Automation::TRIGGERS,
            'stepTypes' => Automation::STEP_TYPES,
        ]);
    }

    public function update(Request $request, Automation $automation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'required|string|in:' . implode(',', array_keys(Automation::TRIGGERS)),
            'trigger_config' => 'nullable|array',
            'is_active' => 'boolean',
            'allow_reentry' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.type' => 'required|string|in:' . implode(',', array_keys(Automation::STEP_TYPES)),
            'steps.*.config' => 'required|array',
        ]);

        $automation->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_type' => $validated['trigger_type'],
            'trigger_config' => $validated['trigger_config'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'allow_reentry' => $request->boolean('allow_reentry'),
        ]);

        // Rebuild steps
        $automation->steps()->delete();
        foreach ($validated['steps'] as $i => $step) {
            AutomationStep::create([
                'automation_id' => $automation->id,
                'position' => $i,
                'type' => $step['type'],
                'config' => $step['config'],
            ]);
        }

        return redirect()->route('admin.automations-engine.show', $automation)->with('success', 'Automatización actualizada');
    }

    public function destroy(Automation $automation)
    {
        $automation->delete();
        return redirect()->route('admin.automations-engine.index')->with('success', 'Automatizacion eliminada');
    }

    public function toggle(Automation $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);
        return back()->with('success', $automation->is_active ? 'Automatizacion activada' : 'Automatizacion pausada');
    }

    /**
     * Manually enroll clients into an automation.
     */
    public function enrollClients(Request $request, Automation $automation, AutomationEngine $engine)
    {
        $clientIds = $request->input('client_ids', []);
        $enrolled = 0;

        foreach ($clientIds as $id) {
            $client = Client::find($id);
            if ($client && $engine->enroll($automation, $client)) {
                $enrolled++;
            }
        }

        return back()->with('success', "{$enrolled} clientes inscritos en la automatizacion.");
    }
}
