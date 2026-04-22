<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarouselTemplateController extends Controller
{
    public function index()
    {
        $templates = CarouselTemplate::orderBy('sort_order')->get();
        return view('admin.carousels.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.carousels.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'slug'            => 'nullable|string|max:80|unique:carousel_templates,slug',
            'description'     => 'nullable|string',
            'blade_view'      => 'required|string|max:150',
            'canvas_size'     => 'required|string|max:20',
            'supported_types' => 'nullable|array',
            'default_vars'    => 'nullable|string',
            'sort_order'      => 'nullable|integer',
            'is_active'       => 'boolean',
        ]);

        $data['slug']      = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        if (isset($data['default_vars'])) {
            $decoded = json_decode($data['default_vars'], true);
            $data['default_vars'] = is_array($decoded) ? $decoded : null;
        }

        CarouselTemplate::create($data);

        return redirect()
            ->route('admin.carousels.templates.index')
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(CarouselTemplate $template)
    {
        return view('admin.carousels.templates.edit', compact('template'));
    }

    public function update(Request $request, CarouselTemplate $template)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'slug'            => 'required|string|max:80|unique:carousel_templates,slug,' . $template->id,
            'description'     => 'nullable|string',
            'blade_view'      => 'required|string|max:150',
            'canvas_size'     => 'required|string|max:20',
            'supported_types' => 'nullable|array',
            'default_vars'    => 'nullable|string',
            'sort_order'      => 'nullable|integer',
            'is_active'       => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if (isset($data['default_vars'])) {
            $decoded = json_decode($data['default_vars'], true);
            $data['default_vars'] = is_array($decoded) ? $decoded : null;
        }

        $template->update($data);

        return redirect()
            ->route('admin.carousels.templates.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy(CarouselTemplate $template)
    {
        if ($template->posts()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay carruseles usando esta plantilla.');
        }

        $template->delete();

        return redirect()
            ->route('admin.carousels.templates.index')
            ->with('success', 'Plantilla eliminada.');
    }
}
