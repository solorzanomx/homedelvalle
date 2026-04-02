<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use Illuminate\Http\Request;

class BrokerController extends Controller
{
    public function index(Request $request)
    {
        $query = Broker::withCount(['clients', 'properties']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $brokers = $query->latest()->paginate(12)->withQueryString();
        return view('brokers.index', compact('brokers'));
    }

    public function create()
    {
        return view('brokers.create');
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('brokers', 'public');
        }

        Broker::create($validated);
        return redirect()->route('brokers.index')->with('success', 'Broker creado exitosamente');
    }

    public function show(string $id)
    {
        $broker = Broker::findOrFail($id);
        return redirect()->route('brokers.edit', $broker);
    }

    public function edit(string $id)
    {
        $broker = Broker::findOrFail($id);
        return view('brokers.edit', compact('broker'));
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('brokers', 'public');
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
