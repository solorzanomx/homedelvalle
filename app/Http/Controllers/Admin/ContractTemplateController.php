<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractTemplate;
use Illuminate\Http\Request;

class ContractTemplateController extends Controller
{
    public function index()
    {
        $templates = ContractTemplate::latest()->get();
        return view('admin.contract-templates.index', compact('templates'));
    }

    public function create()
    {
        $types = ContractTemplate::TYPES;
        $variables = ContractTemplate::DEFAULT_VARIABLES;
        return view('admin.contract-templates.create', compact('types', 'variables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(ContractTemplate::TYPES)),
            'body' => 'required|string',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['variables'] = array_keys(ContractTemplate::DEFAULT_VARIABLES);

        ContractTemplate::create($validated);

        return redirect()->route('admin.contract-templates.index')->with('success', 'Plantilla de contrato creada.');
    }

    public function edit(ContractTemplate $contract_template)
    {
        $types = ContractTemplate::TYPES;
        $variables = ContractTemplate::DEFAULT_VARIABLES;
        return view('admin.contract-templates.edit', compact('contract_template', 'types', 'variables'));
    }

    public function update(Request $request, ContractTemplate $contract_template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(ContractTemplate::TYPES)),
            'body' => 'required|string',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $contract_template->update($validated);

        return redirect()->route('admin.contract-templates.index')->with('success', 'Plantilla actualizada.');
    }

    public function destroy(ContractTemplate $contract_template)
    {
        $contract_template->delete();
        return redirect()->route('admin.contract-templates.index')->with('success', 'Plantilla eliminada.');
    }

    public function preview(ContractTemplate $contract_template)
    {
        return response($contract_template->body)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
