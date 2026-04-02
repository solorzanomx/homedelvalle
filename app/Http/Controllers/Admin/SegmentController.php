<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use App\Services\SegmentService;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function __construct(private SegmentService $segmentService) {}

    public function index()
    {
        $segments = Segment::withCount('clients')
            ->orderBy('name')
            ->get();

        return view('admin.segments.index', compact('segments'));
    }

    public function create()
    {
        return view('admin.segments.form', [
            'segment' => null,
            'fields' => Segment::FIELDS,
            'operators' => Segment::OPERATORS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'rules' => 'required|array|min:1',
            'rules.*.field' => 'required|string',
            'rules.*.operator' => 'required|string',
            'rules.*.value' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $segment = Segment::create($validated);

        // Evaluate immediately
        $this->segmentService->evaluate($segment);

        return redirect()->route('admin.segments.index')->with('success', 'Segmento creado correctamente');
    }

    public function edit(Segment $segment)
    {
        return view('admin.segments.form', [
            'segment' => $segment,
            'fields' => Segment::FIELDS,
            'operators' => Segment::OPERATORS,
        ]);
    }

    public function update(Request $request, Segment $segment)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'rules' => 'required|array|min:1',
            'rules.*.field' => 'required|string',
            'rules.*.operator' => 'required|string',
            'rules.*.value' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $segment->update($validated);

        $this->segmentService->evaluate($segment);

        return redirect()->route('admin.segments.index')->with('success', 'Segmento actualizado');
    }

    public function destroy(Segment $segment)
    {
        if ($segment->is_system) {
            return back()->with('error', 'No se puede eliminar un segmento del sistema.');
        }
        $segment->delete();
        return redirect()->route('admin.segments.index')->with('success', 'Segmento eliminado');
    }

    public function preview(Request $request)
    {
        $rules = $request->input('rules', []);
        $clients = $this->segmentService->preview($rules);
        return response()->json(['clients' => $clients, 'count' => $clients->count()]);
    }

    public function evaluate(Segment $segment)
    {
        $result = $this->segmentService->evaluate($segment);
        return back()->with('success', "Segmento evaluado: {$result['entered']} nuevos, {$result['exited']} salieron.");
    }
}
