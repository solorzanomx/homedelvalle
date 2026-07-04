<?php

namespace App\Http\Controllers;

use App\Models\ProviderCompany;
use App\Models\ProviderContact;
use Illuminate\Http\Request;

class ProviderContactController extends Controller
{
    public function store(Request $request, ProviderCompany $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'in:active,inactive',
        ]);

        $provider->contacts()->create($validated);
        return back()->with('success', 'Contacto agregado exitosamente');
    }

    public function update(Request $request, ProviderContact $contact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'in:active,inactive',
        ]);

        $contact->update($validated);
        return back()->with('success', 'Contacto actualizado exitosamente');
    }

    public function destroy(ProviderContact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contacto eliminado exitosamente');
    }
}
