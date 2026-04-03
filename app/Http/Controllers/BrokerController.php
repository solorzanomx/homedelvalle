<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\BrokerCompany;
use Illuminate\Http\Request;

class BrokerController extends Controller
{
    public function index(Request $request)
    {
        $query = Broker::with('company')->withCount(['clients', 'properties', 'deals', 'operations']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($companyId = $request->input('company')) {
            $query->where('broker_company_id', $companyId);
        }

        $brokers = $query->latest()->paginate(12)->withQueryString();

        $companies = BrokerCompany::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total' => Broker::count(),
            'active' => Broker::where('status', 'active')->count(),
            'operations' => \App\Models\Operation::whereNotNull('broker_id')->count(),
            'commission' => \App\Models\Commission::where('status', 'paid')->sum('amount'),
        ];

        return view('brokers.index', compact('brokers', 'companies', 'stats'));
    }

    public function create()
    {
        $companies = BrokerCompany::where('status', 'active')->orderBy('name')->get();
        return view('brokers.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:brokers',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|unique:brokers',
            'commission_rate' => 'nullable|numeric|between:0,100',
            'broker_company_id' => 'nullable|exists:broker_companies,id',
            'company_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:255',
            'referral_source' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'status' => 'in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('brokers', 'public');
        }

        // Auto-fill company_name from company relationship
        if (!empty($validated['broker_company_id']) && empty($validated['company_name'])) {
            $company = BrokerCompany::find($validated['broker_company_id']);
            if ($company) $validated['company_name'] = $company->name;
        }

        Broker::create($validated);
        return redirect()->route('brokers.index')->with('success', 'Broker creado exitosamente');
    }

    public function show(string $id)
    {
        $broker = Broker::with(['company', 'clients', 'properties', 'deals', 'operations', 'commissions'])
            ->withCount(['clients', 'properties', 'deals', 'operations'])
            ->findOrFail($id);

        $totalCommission = $broker->commissions->sum('amount');

        return view('brokers.show', compact('broker', 'totalCommission'));
    }

    public function edit(string $id)
    {
        $broker = Broker::findOrFail($id);
        $companies = BrokerCompany::where('status', 'active')->orderBy('name')->get();
        return view('brokers.edit', compact('broker', 'companies'));
    }

    public function update(Request $request, string $id)
    {
        $broker = Broker::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:brokers,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|unique:brokers,license_number,' . $id,
            'commission_rate' => 'nullable|numeric|between:0,100',
            'broker_company_id' => 'nullable|exists:broker_companies,id',
            'company_name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:255',
            'referral_source' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'status' => 'in:active,inactive',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('brokers', 'public');
        }

        if (!empty($validated['broker_company_id']) && empty($validated['company_name'])) {
            $company = BrokerCompany::find($validated['broker_company_id']);
            if ($company) $validated['company_name'] = $company->name;
        }

        $broker->update($validated);
        return redirect()->route('brokers.edit', $broker)->with('success', 'Broker actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $broker = Broker::findOrFail($id);
        $broker->delete();
        return redirect()->route('brokers.index')->with('success', 'Broker eliminado exitosamente');
    }
}
