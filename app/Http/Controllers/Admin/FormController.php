<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormController extends Controller
{
    public function index()
    {
        $forms = Form::withCount('submissions')->latest()->get();

        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        return view('admin.forms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug',
            'description' => 'nullable|string|max:500',
            'fields_json' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['fields'] = json_decode($validated['fields_json'], true) ?: [];
        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['fields_json']);

        Form::create($validated);

        return redirect()->route('admin.forms.index')->with('success', 'Formulario creado correctamente.');
    }

    public function edit(Form $form)
    {
        return view('admin.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:forms,slug,' . $form->id,
            'description' => 'nullable|string|max:500',
            'fields_json' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['fields'] = json_decode($validated['fields_json'], true) ?: [];
        $validated['is_active'] = $request->boolean('is_active');
        unset($validated['fields_json']);

        $form->update($validated);

        return redirect()->route('admin.forms.index')->with('success', 'Formulario actualizado correctamente.');
    }

    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()->route('admin.forms.index')->with('success', 'Formulario eliminado correctamente.');
    }

    public function submissions(Form $form)
    {
        $submissions = $form->submissions()->latest()->paginate(20);

        return view('admin.forms.submissions', compact('form', 'submissions'));
    }
}
