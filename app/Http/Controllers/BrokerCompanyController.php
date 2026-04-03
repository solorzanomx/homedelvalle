<?php

namespace App\Http\Controllers;

use App\Models\BrokerCompany;
use Illuminate\Http\Request;

class BrokerCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = BrokerCompany::withCount('brokers');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $companies = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => BrokerCompany::count(),
            'active' => BrokerCompany::where('status', 'active')->count(),
            'brokers' => \App\Models\Broker::whereNotNull('broker_company_id')->count(),
        ];

        return view('broker-companies.index', compact('companies', 'stats'));
    }

    public function create()
    {
        return view('broker-companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('broker-companies', 'public');
        }

        BrokerCompany::create($validated);
        return redirect()->route('broker-companies.index')->with('success', 'Empresa creada exitosamente');
    }

    public function show(string $id)
    {
        return redirect()->route('broker-companies.edit', $id);
    }

    public function edit(string $id)
    {
        $company = BrokerCompany::withCount('brokers')->findOrFail($id);
        return view('broker-companies.edit', compact('company'));
    }

    public function update(Request $request, string $id)
    {
        $company = BrokerCompany::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('broker-companies', 'public');
        }

        $company->update($validated);
        return redirect()->route('broker-companies.edit', $company)->with('success', 'Empresa actualizada exitosamente');
    }

    public function destroy(string $id)
    {
        $company = BrokerCompany::findOrFail($id);
        $company->delete();
        return redirect()->route('broker-companies.index')->with('success', 'Empresa eliminada exitosamente');
    }
}
