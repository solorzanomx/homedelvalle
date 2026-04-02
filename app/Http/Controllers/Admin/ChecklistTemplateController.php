<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Models\StageChecklistTemplate;
use Illuminate\Http\Request;

class ChecklistTemplateController extends Controller
{
    public function index()
    {
        $templates = StageChecklistTemplate::orderBy('stage')
            ->orderBy('operation_type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('stage');

        $stages = Operation::STAGES;

        return view('admin.checklists.index', compact('templates', 'stages'));
    }

    public function create()
    {
        $stages = Operation::STAGES;
        return view('admin.checklists.create', compact('stages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys(Operation::STAGES)),
            'operation_type' => 'required|in:venta,renta,captacion,both',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable',
            'is_active' => 'nullable',
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        StageChecklistTemplate::create($validated);

        return redirect()->route('admin.checklists.index')->with('success', 'Item de checklist creado.');
    }

    public function edit(StageChecklistTemplate $checklist)
    {
        $stages = Operation::STAGES;
        return view('admin.checklists.edit', compact('checklist', 'stages'));
    }

    public function update(Request $request, StageChecklistTemplate $checklist)
    {
        $validated = $request->validate([
            'stage' => 'required|in:' . implode(',', array_keys(Operation::STAGES)),
            'operation_type' => 'required|in:venta,renta,captacion,both',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable',
            'is_active' => 'nullable',
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['is_active'] = $request->boolean('is_active');

        $checklist->update($validated);

        return redirect()->route('admin.checklists.index')->with('success', 'Item actualizado.');
    }

    public function destroy(StageChecklistTemplate $checklist)
    {
        $checklist->delete();
        return redirect()->route('admin.checklists.index')->with('success', 'Item eliminado.');
    }
}
