<?php

namespace App\Http\Controllers;

use App\Models\Referrer;
use App\Models\Referral;
use App\Models\Property;
use App\Models\Operation;
use App\Models\Client;
use Illuminate\Http\Request;

class ReferrerController extends Controller
{
    public function index(Request $request)
    {
        $query = Referrer::withCount('referrals');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $referrers = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => Referrer::count(),
            'active' => Referrer::where('status', 'active')->count(),
            'referrals' => Referral::count(),
            'paid' => Referral::where('status', 'paid')->sum('commission_amount'),
        ];

        return view('referrers.index', compact('referrers', 'stats'));
    }

    public function create()
    {
        return view('referrers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:portero,vecino,broker_hipotecario,comisionista,otro',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        Referrer::create($validated);
        return redirect()->route('referrers.index')->with('success', 'Comisionista creado exitosamente');
    }

    public function show(string $id)
    {
        $referrer = Referrer::withCount('referrals')->findOrFail($id);
        $referrals = Referral::where('referrer_id', $id)
            ->with(['property', 'operation', 'client'])
            ->latest()
            ->paginate(10);

        $properties = Property::where('status', 'available')->orderBy('title')->get(['id', 'title']);
        $operations = Operation::whereIn('status', ['active', 'in_progress'])->latest()->get(['id', 'type', 'stage']);
        $clients = Client::orderBy('name')->get(['id', 'name']);

        return view('referrers.show', compact('referrer', 'referrals', 'properties', 'operations', 'clients'));
    }

    public function edit(string $id)
    {
        $referrer = Referrer::findOrFail($id);
        return view('referrers.edit', compact('referrer'));
    }

    public function update(Request $request, string $id)
    {
        $referrer = Referrer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'type' => 'required|in:portero,vecino,broker_hipotecario,comisionista,otro',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'in:active,inactive',
        ]);

        $referrer->update($validated);
        return redirect()->route('referrers.edit', $referrer)->with('success', 'Comisionista actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $referrer = Referrer::findOrFail($id);
        $referrer->delete();
        return redirect()->route('referrers.index')->with('success', 'Comisionista eliminado exitosamente');
    }

    public function storeReferral(Request $request, string $referrerId)
    {
        $referrer = Referrer::findOrFail($referrerId);

        $validated = $request->validate([
            'property_id' => 'nullable|exists:properties,id',
            'operation_id' => 'nullable|exists:operations,id',
            'client_id' => 'nullable|exists:clients,id',
            'commission_percentage' => 'required|numeric|between:0,100',
            'commission_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['referrer_id'] = $referrer->id;

        Referral::create($validated);

        $referrer->increment('total_referrals');

        return redirect()->route('referrers.show', $referrer)->with('success', 'Referido registrado exitosamente');
    }

    public function updateReferralStatus(Request $request, string $referralId)
    {
        $referral = Referral::findOrFail($referralId);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,paid',
        ]);

        $referral->update($validated);

        if ($validated['status'] === 'paid' && !$referral->paid_at) {
            $referral->update(['paid_at' => now()]);
            $referral->referrer->increment('total_earned', $referral->commission_amount);
        }

        return redirect()->back()->with('success', 'Estado actualizado');
    }
}
