<?php

namespace App\Http\Controllers;

use App\Models\ProviderCompany;
use Illuminate\Http\Request;

class ProviderCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = ProviderCompany::withCount('contacts');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
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
            'total' => ProviderCompany::count(),
            'active' => ProviderCompany::where('status', 'active')->count(),
            'contacts' => \App\Models\ProviderContact::count(),
        ];

        return view('providers.index', compact('companies', 'stats'));
    }

    public function create()
    {
        return view('providers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(ProviderCompany::TYPES)),
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $company = ProviderCompany::create($validated);
        return redirect()->route('providers.show', $company)->with('success', 'Proveedor creado exitosamente');
    }

    public function show(string $id)
    {
        $company = ProviderCompany::withCount('contacts')->findOrFail($id);
        $contacts = $company->contacts()->orderBy('name')->get();
        $charges = $company->charges()
            ->with(['operation.property', 'operation.client', 'rentalProcess.property', 'rentalProcess.ownerClient'])
            ->latest()
            ->get();

        $totals = [
            'cargo' => $charges->where('flow', 'cargo')->sum(fn ($c) => $c->amount ?? $c->calculateCommission()),
            'comision' => $charges->where('flow', 'comision')->sum(fn ($c) => $c->amount ?? $c->calculateCommission()),
        ];

        return view('providers.show', compact('company', 'contacts', 'charges', 'totals'));
    }

    public function edit(string $id)
    {
        $company = ProviderCompany::withCount('contacts')->findOrFail($id);
        return view('providers.edit', compact('company'));
    }

    public function update(Request $request, string $id)
    {
        $company = ProviderCompany::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(ProviderCompany::TYPES)),
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $company->update($validated);
        return redirect()->route('providers.show', $company)->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $company = ProviderCompany::findOrFail($id);
        $company->delete();
        return redirect()->route('providers.index')->with('success', 'Proveedor eliminado exitosamente');
    }
}
