<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\LeadEvent;
use App\Services\AutomationEngine;
use App\Services\LeadScoringService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index()
    {
        $deals = Deal::with(['property', 'client', 'broker'])->latest()->paginate(20);
        $stages = ['lead' => 'Lead', 'contact' => 'Contacto', 'visit' => 'Visita', 'negotiation' => 'Negociacion', 'offer' => 'Oferta', 'closing' => 'Cierre', 'won' => 'Ganado', 'lost' => 'Perdido'];
        $dealsByStage = [];
        foreach (array_keys($stages) as $stage) {
            $dealsByStage[$stage] = Deal::with(['property', 'client', 'broker'])->where('stage', $stage)->latest()->get();
        }
        $stats = [
            'total' => Deal::count(),
            'active' => Deal::whereNotIn('stage', ['won', 'lost'])->count(),
            'won' => Deal::where('stage', 'won')->count(),
            'value' => Deal::whereNotIn('stage', ['lost'])->sum('amount'),
        ];
        return view('deals.index', compact('deals', 'stages', 'dealsByStage', 'stats'));
    }

    public function create()
    {
        $properties = Property::all();
        $clients = Client::all();
        $brokers = Broker::all();
        $stages = ['lead' => 'Lead', 'contact' => 'Contacto', 'visit' => 'Visita', 'negotiation' => 'Negociacion', 'offer' => 'Oferta', 'closing' => 'Cierre', 'won' => 'Ganado', 'lost' => 'Perdido'];
        return view('deals.create', compact('properties', 'clients', 'brokers', 'stages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'stage' => 'required|in:lead,contact,visit,negotiation,offer,closing,won,lost',
            'amount' => 'required|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expected_close_date' => 'nullable|date',
        ]);

        if (in_array($validated['stage'], ['won', 'lost'])) {
            $validated['closed_at'] = now();
        }

        Deal::create($validated);

        // Score deal creation
        if (!empty($validated['client_id'])) {
            app(LeadScoringService::class)->processEvent($validated['client_id'], 'deal_created', ['source' => 'manual']);
            LeadEvent::record($validated['client_id'], 'deal_created', ['source' => 'manual']);
        }

        return redirect()->route('deals.index')->with('success', 'Deal creado exitosamente');
    }

    public function show(string $id)
    {
        return redirect()->route('deals.edit', $id);
    }

    public function edit(string $id)
    {
        $deal = Deal::with(['property', 'client', 'broker'])->findOrFail($id);
        $properties = Property::all();
        $clients = Client::all();
        $brokers = Broker::all();
        $stages = ['lead' => 'Lead', 'contact' => 'Contacto', 'visit' => 'Visita', 'negotiation' => 'Negociacion', 'offer' => 'Oferta', 'closing' => 'Cierre', 'won' => 'Ganado', 'lost' => 'Perdido'];
        return view('deals.edit', compact('deal', 'properties', 'clients', 'brokers', 'stages'));
    }

    public function update(Request $request, string $id)
    {
        $deal = Deal::findOrFail($id);
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'client_id' => 'required|exists:clients,id',
            'broker_id' => 'nullable|exists:brokers,id',
            'stage' => 'required|in:lead,contact,visit,negotiation,offer,closing,won,lost',
            'amount' => 'required|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expected_close_date' => 'nullable|date',
        ]);

        if (in_array($validated['stage'], ['won', 'lost']) && !$deal->closed_at) {
            $validated['closed_at'] = now();
        } elseif (!in_array($validated['stage'], ['won', 'lost'])) {
            $validated['closed_at'] = null;
        }

        $deal->update($validated);
        return redirect()->route('deals.edit', $deal)->with('success', 'Deal actualizado exitosamente');
    }

    public function updateStage(Request $request, string $id)
    {
        $deal = Deal::findOrFail($id);
        $oldStage = $deal->stage;

        $validated = $request->validate([
            'stage' => 'required|in:lead,contact,visit,negotiation,offer,closing,won,lost',
        ]);

        $data = ['stage' => $validated['stage']];
        if (in_array($validated['stage'], ['won', 'lost']) && !$deal->closed_at) {
            $data['closed_at'] = now();
        } elseif (!in_array($validated['stage'], ['won', 'lost'])) {
            $data['closed_at'] = null;
        }

        $deal->update($data);

        // Fire stage_change for automations + scoring
        if ($oldStage !== $validated['stage'] && $deal->client_id) {
            $client = Client::find($deal->client_id);
            if ($client) {
                LeadEvent::record($deal->client_id, 'stage_changed', [
                    'source' => 'deal',
                    'properties' => ['from_stage' => $oldStage, 'to_stage' => $validated['stage']],
                ]);
                app(LeadScoringService::class)->processEvent($deal->client_id, 'stage_changed', ['source' => 'deal']);
                app(AutomationEngine::class)->processStageChange($client, $oldStage, $validated['stage'], 'deal');
            }
        }

        return redirect()->back()->with('success', 'Etapa actualizada');
    }

    public function destroy(string $id)
    {
        Deal::findOrFail($id)->delete();
        return redirect()->route('deals.index')->with('success', 'Deal eliminado exitosamente');
    }
}
