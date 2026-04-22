<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Valuation\RecordClosedSaleAction;
use App\Http\Controllers\Controller;
use App\Models\MarketColonia;
use App\Models\MarketComparable;
use App\Models\MarketZone;
use App\Models\Property;
use App\Models\PropertyValuation;
use App\Services\Valuation\ValuationEngine;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ValuationController extends Controller
{
    public function __construct(private ValuationEngine $engine) {}

    public function index(Request $request): View
    {
        $valuations = PropertyValuation::with(['property', 'creator', 'colonia.zone'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('diagnosis'), fn($q) => $q->where('diagnosis', $request->diagnosis))
            ->latest()
            ->paginate(20);

        return view('admin.valuations.index', compact('valuations'));
    }

    public function create(Request $request): View
    {
        $property = $request->filled('property')
            ? Property::findOrFail($request->property)
            : null;

        $colonias = MarketColonia::with('zone')
            ->published()
            ->orderBy('name')
            ->get()
            ->groupBy('zone.name');

        return view('admin.valuations.form', compact('property', 'colonias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'property_id'          => 'nullable|exists:properties,id',
            'input_colonia_id'     => 'nullable|exists:market_colonias,id',
            'input_colonia_raw'    => 'nullable|string|max:150',
            'input_type'           => 'required|in:apartment,house,land,office',
            'input_m2_total'       => 'required|numeric|min:10|max:5000',
            'input_m2_const'       => 'nullable|numeric|min:10|max:5000',
            'input_age_years'      => 'required|integer|min:0|max:150',
            'input_condition'      => 'required|in:excellent,good,fair,poor',
            'input_bedrooms'       => 'required|integer|min:0|max:20',
            'input_bathrooms'      => 'required|integer|min:0|max:20',
            'input_parking'        => 'required|integer|min:0|max:10',
            'input_floor'          => 'nullable|integer|min:1|max:50',
            'input_has_elevator'   => 'boolean',
            'input_has_rooftop'    => 'boolean',
            'input_has_balcony'    => 'boolean',
            'input_has_service_room' => 'boolean',
            'input_has_storage'    => 'boolean',
            'input_notes'          => 'nullable|string|max:1000',
        ]);

        $data['created_by']           = auth()->id();
        $data['input_has_elevator']   = $request->boolean('input_has_elevator');
        $data['input_has_rooftop']    = $request->boolean('input_has_rooftop');
        $data['input_has_balcony']    = $request->boolean('input_has_balcony');
        $data['input_has_service_room'] = $request->boolean('input_has_service_room');
        $data['input_has_storage']    = $request->boolean('input_has_storage');

        $valuation = PropertyValuation::create($data);

        // Calcular inmediatamente
        $result = $this->engine->calculate($valuation);

        if ($result->isInsufficient()) {
            return redirect()
                ->route('admin.valuations.show', $valuation)
                ->with('warning', 'Valuación creada, pero no se encontraron datos de mercado para esta colonia. Puedes continuar cuando haya precios registrados.');
        }

        return redirect()
            ->route('admin.valuations.show', $valuation)
            ->with('success', 'Valuación calculada correctamente.');
    }

    public function show(PropertyValuation $valuation): View
    {
        $valuation->load(['property', 'creator', 'colonia.zone', 'snapshot', 'adjustments', 'comparables']);

        return view('admin.valuations.show', compact('valuation'));
    }

    public function edit(PropertyValuation $valuation): View
    {
        $colonias = MarketColonia::with('zone')
            ->published()
            ->orderBy('name')
            ->get()
            ->groupBy('zone.name');

        $property = $valuation->property;

        return view('admin.valuations.form', compact('valuation', 'colonias', 'property'));
    }

    public function update(Request $request, PropertyValuation $valuation): RedirectResponse
    {
        $data = $request->validate([
            'input_colonia_id'     => 'nullable|exists:market_colonias,id',
            'input_colonia_raw'    => 'nullable|string|max:150',
            'input_type'           => 'required|in:apartment,house,land,office',
            'input_m2_total'       => 'required|numeric|min:10|max:5000',
            'input_m2_const'       => 'nullable|numeric|min:10|max:5000',
            'input_age_years'      => 'required|integer|min:0|max:150',
            'input_condition'      => 'required|in:excellent,good,fair,poor',
            'input_bedrooms'       => 'required|integer|min:0|max:20',
            'input_bathrooms'      => 'required|integer|min:0|max:20',
            'input_parking'        => 'required|integer|min:0|max:10',
            'input_floor'          => 'nullable|integer|min:1|max:50',
            'input_has_elevator'   => 'boolean',
            'input_has_rooftop'    => 'boolean',
            'input_has_balcony'    => 'boolean',
            'input_has_service_room' => 'boolean',
            'input_has_storage'    => 'boolean',
            'input_notes'          => 'nullable|string|max:1000',
        ]);

        $data['input_has_elevator']    = $request->boolean('input_has_elevator');
        $data['input_has_rooftop']     = $request->boolean('input_has_rooftop');
        $data['input_has_balcony']     = $request->boolean('input_has_balcony');
        $data['input_has_service_room']= $request->boolean('input_has_service_room');
        $data['input_has_storage']     = $request->boolean('input_has_storage');

        $valuation->update($data);

        $result = $this->engine->calculate($valuation->fresh());

        $msg = $result->isInsufficient()
            ? 'Datos actualizados. Sin precios de mercado para recalcular.'
            : 'Valuación recalculada correctamente.';

        return redirect()
            ->route('admin.valuations.show', $valuation)
            ->with('success', $msg);
    }

    /** Cambiar estado: draft → final → delivered */
    public function updateStatus(Request $request, PropertyValuation $valuation): RedirectResponse
    {
        $request->validate(['status' => 'required|in:draft,final,delivered']);

        $valuation->update([
            'status'       => $request->status,
            'delivered_at' => $request->status === 'delivered' ? now() : $valuation->delivered_at,
        ]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function destroy(PropertyValuation $valuation): RedirectResponse
    {
        $valuation->delete();

        return redirect()
            ->route('admin.valuations.index')
            ->with('success', 'Valuación eliminada.');
    }

    /** Generate and stream valuation PDF */
    public function pdf(PropertyValuation $valuation)
    {
        $valuation->load(['property', 'colonia.zone', 'adjustments']);

        $html = view('admin.valuations.pdf', compact('valuation'))->render();

        $pdf = Browsershot::html($html)
            ->setChromePath(config('browsershot.chrome_path'))
            ->setNodeBinary(config('browsershot.node_path'))
            ->setNpmBinary(config('browsershot.npm_path'))
            ->noSandbox()
            ->format('A4')
            ->pdf();

        $slug     = Str::slug($valuation->colonia?->name ?? $valuation->input_colonia_raw ?? 'inmueble');
        $filename = 'Opinion-de-Valor-' . $slug . '-' . now()->format('Y-m-d') . '.pdf';

        // Save path for future reference
        $valuation->update(['pdf_path' => 'valuations/' . $filename]);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /** Manual sale recording — for closed deals not tracked in Operations */
    public function recordSale(Request $request, PropertyValuation $valuation, RecordClosedSaleAction $action): RedirectResponse
    {
        $request->validate([
            'actual_sale_price' => 'required|numeric|min:1',
            'closed_at'         => 'nullable|date',
        ]);

        $action->execute(
            $valuation,
            (float) $request->actual_sale_price,
            $request->filled('closed_at') ? \Carbon\Carbon::parse($request->closed_at) : null,
        );

        return back()->with('success', 'Precio de cierre registrado. Comparable guardado.');
    }

    /** Analytics dashboard — accuracy tracking by zone */
    public function analytics(): View
    {
        $closedValuations = PropertyValuation::with('colonia.zone')
            ->whereNotNull('actual_sale_price')
            ->whereNotNull('accuracy_pct')
            ->latest('sale_recorded_at')
            ->get();

        // Per-zone stats
        $zoneStats = $closedValuations
            ->groupBy(fn($v) => $v->colonia?->zone?->name ?? 'Sin zona')
            ->map(fn($group) => [
                'count'        => $group->count(),
                'avg_accuracy' => round($group->avg('accuracy_pct'), 1),
                'avg_sale'     => (int) $group->avg('actual_sale_price'),
                'above_count'  => $group->where('accuracy_pct', '>', 0)->count(),
                'below_count'  => $group->where('accuracy_pct', '<', 0)->count(),
            ]);

        // Own comparables count
        $ownComparables = MarketComparable::where('source', 'own')->count();

        return view('admin.valuations.analytics', compact('closedValuations', 'zoneStats', 'ownComparables'));
    }
}
