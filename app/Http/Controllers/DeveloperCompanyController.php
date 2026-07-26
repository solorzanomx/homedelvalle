<?php

namespace App\Http\Controllers;

use App\Models\DeveloperCompany;
use Illuminate\Http\Request;

class DeveloperCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = DeveloperCompany::withCount('contacts');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('rfc', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $companies = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total'    => DeveloperCompany::count(),
            'active'   => DeveloperCompany::where('status', 'active')->count(),
            'contacts' => \App\Models\DeveloperContact::count(),
        ];

        return view('developers.index', compact('companies', 'stats'));
    }

    public function create()
    {
        return view('developers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(DeveloperCompany::TYPES)),
            'rfc' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $company = DeveloperCompany::create($validated);
        return redirect()->route('developers.show', $company)->with('success', 'Constructora creada exitosamente');
    }

    public function show(string $id)
    {
        $company = DeveloperCompany::withCount('contacts')->findOrFail($id);
        $contacts = $company->contacts()->orderBy('name')->get();

        return view('developers.show', compact('company', 'contacts'));
    }

    public function edit(string $id)
    {
        $company = DeveloperCompany::withCount('contacts')->findOrFail($id);
        return view('developers.edit', compact('company'));
    }

    public function update(Request $request, string $id)
    {
        $company = DeveloperCompany::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(DeveloperCompany::TYPES)),
            'rfc' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $company->update($validated);
        return redirect()->route('developers.show', $company)->with('success', 'Constructora actualizada exitosamente');
    }

    public function destroy(string $id)
    {
        $company = DeveloperCompany::findOrFail($id);
        $company->delete();
        return redirect()->route('developers.index')->with('success', 'Constructora eliminada exitosamente');
    }
}
