<?php

namespace App\Http\Controllers;

use App\Models\DeveloperCompany;
use App\Models\DeveloperContact;
use Illuminate\Http\Request;

class DeveloperContactController extends Controller
{
    public function index(Request $request)
    {
        $query = DeveloperContact::with('developerCompany');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('developerCompany', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($zone = $request->input('zone')) {
            $query->whereJsonContains('interest_zones', $zone);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $contacts = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('developers.contacts.index', compact('contacts'));
    }

    public function create(Request $request)
    {
        $companies = DeveloperCompany::where('status', 'active')->orderBy('name')->get();
        $selectedCompanyId = $request->integer('developer_company_id') ?: null;

        return view('developers.contacts.form', ['contact' => null, 'companies' => $companies, 'selectedCompanyId' => $selectedCompanyId]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        DeveloperContact::create($validated);

        return redirect()->route('developer-contacts.index')->with('success', 'Contacto agregado exitosamente');
    }

    public function edit(DeveloperContact $developer_contact)
    {
        $companies = DeveloperCompany::where('status', 'active')->orderBy('name')->get();

        return view('developers.contacts.form', [
            'contact' => $developer_contact,
            'companies' => $companies,
            'selectedCompanyId' => $developer_contact->developer_company_id,
        ]);
    }

    public function update(Request $request, DeveloperContact $developer_contact)
    {
        $validated = $this->validated($request);

        $developer_contact->update($validated);

        return redirect()->route('developer-contacts.index')->with('success', 'Contacto actualizado exitosamente');
    }

    public function destroy(DeveloperContact $developer_contact)
    {
        $developer_contact->delete();
        return back()->with('success', 'Contacto eliminado exitosamente');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'developer_company_id' => 'nullable|exists:developer_companies,id',
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'interest_zones' => 'nullable|string',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        // El input llega como texto separado por comas (ej. "Del Valle, Narvarte, Nápoles")
        $zonesInput = $data['interest_zones'] ?? '';
        $data['interest_zones'] = $zonesInput !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $zonesInput))))
            : null;

        return $data;
    }
}
