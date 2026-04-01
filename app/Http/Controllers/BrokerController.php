<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use Illuminate\Http\Request;

class BrokerController extends Controller
{
    public function index()
    {
        $brokers = Broker::all();
        return view('brokers.index', compact('brokers'));
    }

    public function create()
    {
        return view('brokers.create-improved');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:brokers',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|unique:brokers',
            'commission_rate' => 'nullable|numeric|between:0,100',
            'company_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        Broker::create($validated);
        return redirect('/')->with('success', 'Broker creado exitosamente');
    }

    public function show(string $id)
    {
        $broker = Broker::findOrFail($id);
        return view('brokers.show', compact('broker'));
    }

    public function edit(string $id)
    {
        $broker = Broker::findOrFail($id);
        return view('brokers.edit-improved', compact('broker'));
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
            'company_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $broker->update($validated);
        return redirect('/')->with('success', 'Broker actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $broker = Broker::findOrFail($id);
        $broker->delete();
        return redirect('/')->with('success', 'Broker eliminado exitosamente');
    }
}
