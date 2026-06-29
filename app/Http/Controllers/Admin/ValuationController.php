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
            ? Property::with('marketColonia')->findOrFail($request->property)
            : null;

        $colonias = MarketColonia::with('zone')
            ->published()
            ->orderBy('name')
            ->get()
            ->groupBy('zone.name');

        // Pre-fill values from property if provided
        $prefill = [];
        if ($property) {
            $prefill = [
                'input_colonia_id'    => $property->market_colonia_id,
                'input_bedrooms'      => $property->bedrooms,
                'input_bathrooms'     => $property->bathrooms,
                'input_parking'       => $property->parking,
                'input_area_total'    => $property->area,
                'input_area_privada'  => $property->construction_area,
                'input_type'          => match($property->property_type) {
                    'Apartment' => 'apartment',
                    'House'     => 'house',
                    'Land'      => 'land',
                    'Office'    => 'office',
                    default     => null,
                },
                'input_age'           => $property->year_built ? (date('Y') - $property->year_built) : null,
            ];
        }

        return view('admin.valuations.form', compact('property', 'colonias', 'prefill'));
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
            'input_half_bathrooms' => 'nullable|integer|min:0|max:10',
            'input_parking'        => 'required|integer|min:0|max:10',
            'input_parking_type'       => 'nullable|in:regular,tandem,lift',
            'input_building_condition' => 'nullable|in:excellent,good,fair,poor',
            'input_floor'          => 'nullable|integer|min:1|max:50',
            'input_has_elevator'   => 'boolean',
            'input_has_rooftop'    => 'boolean',
            'input_has_balcony'    => 'boolean',
            'input_has_service_room' => 'boolean',
            'input_has_storage'    => 'boolean',
            'input_has_doorman'    => 'boolean',
            'input_has_intercom'   => 'boolean',
            'input_has_security_cameras' => 'boolean',
            'input_has_alarm'      => 'boolean',
            'input_has_gym'        => 'boolean',
            'input_has_pool'       => 'boolean',
            'input_has_lobby'      => 'boolean',
            'input_has_natural_gas'=> 'boolean',
            'input_has_cistern'    => 'boolean',
            'input_street_type'    => 'nullable|in:quiet,residential,principal,commercial,dead_end',
            'input_views'          => 'nullable|in:city,park,garden,street,interior',
            'input_legal_status'   => 'nullable|in:clear,mortgage,pending_deed,unknown',
            'input_maintenance_fee'=> 'nullable|integer|min:0|max:99999',
            'input_renovation_year'=> 'nullable|integer|min:1900|max:' . date('Y'),
            'input_unit_position'  => 'nullable|in:exterior,interior',
            'input_orientation'    => 'nullable|in:norte,noreste,este,sureste,sur,suroeste,oeste,noroeste',
            'input_seismic_status' => 'nullable|in:none,damaged_repaired,damaged_reinforced,unknown',
            'input_notes'          => 'nullable|string|max:1000',
        ]);

        $data['created_by']           = auth()->id();
        $data['input_has_elevator']   = $request->boolean('input_has_elevator');
        $data['input_has_rooftop']    = $request->boolean('input_has_rooftop');
        $data['input_has_balcony']    = $request->boolean('input_has_balcony');
        $data['input_has_service_room'] = $request->boolean('input_has_service_room');
        $data['input_has_storage']    = $request->boolean('input_has_storage');
        $data['input_has_doorman']    = $request->boolean('input_has_doorman');
        $data['input_has_intercom']   = $request->boolean('input_has_intercom');
        $data['input_has_security_cameras'] = $request->boolean('input_has_security_cameras');
        $data['input_has_alarm']      = $request->boolean('input_has_alarm');
        $data['input_has_gym']        = $request->boolean('input_has_gym');
        $data['input_has_pool']       = $request->boolean('input_has_pool');
        $data['input_has_lobby']      = $request->boolean('input_has_lobby');
        $data['input_has_natural_gas']= $request->boolean('input_has_natural_gas');
        $data['input_has_cistern']    = $request->boolean('input_has_cistern');
        $data['input_half_bathrooms'] = (int) $request->input('input_half_bathrooms', 0);
        // Nullify apartment-specific fields when not apartment
        if (($data['input_type'] ?? '') !== 'apartment') {
            $data['input_unit_position']  = null;
            $data['input_orientation']    = null;
            $data['input_seismic_status'] = null;
        }
        $data['input_parking_type'] = $request->input('input_parking_type', 'regular');
        // Only save building condition for apartments
        if (($data['input_type'] ?? '') !== 'apartment') {
            $data['input_building_condition'] = null;
        }

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
            'input_half_bathrooms' => 'nullable|integer|min:0|max:10',
            'input_parking'        => 'required|integer|min:0|max:10',
            'input_parking_type'       => 'nullable|in:regular,tandem,lift',
            'input_building_condition' => 'nullable|in:excellent,good,fair,poor',
            'input_floor'          => 'nullable|integer|min:1|max:50',
            'input_has_elevator'   => 'boolean',
            'input_has_rooftop'    => 'boolean',
            'input_has_balcony'    => 'boolean',
            'input_has_service_room' => 'boolean',
            'input_has_storage'    => 'boolean',
            'input_has_doorman'    => 'boolean',
            'input_has_intercom'   => 'boolean',
            'input_has_security_cameras' => 'boolean',
            'input_has_alarm'      => 'boolean',
            'input_has_gym'        => 'boolean',
            'input_has_pool'       => 'boolean',
            'input_has_lobby'      => 'boolean',
            'input_has_natural_gas'=> 'boolean',
            'input_has_cistern'    => 'boolean',
            'input_street_type'    => 'nullable|in:quiet,residential,principal,commercial,dead_end',
            'input_views'          => 'nullable|in:city,park,garden,street,interior',
            'input_legal_status'   => 'nullable|in:clear,mortgage,pending_deed,unknown',
            'input_maintenance_fee'=> 'nullable|integer|min:0|max:99999',
            'input_renovation_year'=> 'nullable|integer|min:1900|max:' . date('Y'),
            'input_unit_position'  => 'nullable|in:exterior,interior',
            'input_orientation'    => 'nullable|in:norte,noreste,este,sureste,sur,suroeste,oeste,noroeste',
            'input_seismic_status' => 'nullable|in:none,damaged_repaired,damaged_reinforced,unknown',
            'input_notes'          => 'nullable|string|max:1000',
        ]);

        $data['input_has_elevator']    = $request->boolean('input_has_elevator');
        $data['input_has_rooftop']     = $request->boolean('input_has_rooftop');
        $data['input_has_balcony']     = $request->boolean('input_has_balcony');
        $data['input_has_service_room']= $request->boolean('input_has_service_room');
        $data['input_has_storage']     = $request->boolean('input_has_storage');
        $data['input_has_doorman']     = $request->boolean('input_has_doorman');
        $data['input_has_intercom']    = $request->boolean('input_has_intercom');
        $data['input_has_security_cameras'] = $request->boolean('input_has_security_cameras');
        $data['input_has_alarm']       = $request->boolean('input_has_alarm');
        $data['input_has_gym']         = $request->boolean('input_has_gym');
        $data['input_has_pool']        = $request->boolean('input_has_pool');
        $data['input_has_lobby']       = $request->boolean('input_has_lobby');
        $data['input_has_natural_gas'] = $request->boolean('input_has_natural_gas');
        $data['input_has_cistern']     = $request->boolean('input_has_cistern');
        $data['input_half_bathrooms']  = (int) $request->input('input_half_bathrooms', 0);
        if (($data['input_type'] ?? '') !== 'apartment') {
            $data['input_unit_position']  = null;
            $data['input_orientation']    = null;
            $data['input_seismic_status'] = null;
        }
        $data['input_parking_type'] = $request->input('input_parking_type', 'regular');
        // Only save building condition for apartments
        if (($data['input_type'] ?? '') !== 'apartment') {
            $data['input_building_condition'] = null;
        }

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

    /** PATCH /{valuation}/authorize — ajuste de precio y autorización para presentación */
    public function authorizePrice(Request $request, PropertyValuation $valuation): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'price_override'       => 'nullable|numeric|min:100000|max:999999999',
            'price_override_notes' => 'nullable|string|max:1000',
            'action'               => 'required|in:save,authorize,clear',
        ]);

        $action = $request->input('action');

        if ($action === 'clear') {
            $valuation->update([
                'price_override'            => null,
                'price_override_notes'      => null,
                'price_override_by'         => null,
                'price_override_at'         => null,
                'price_override_authorized' => false,
            ]);

            return response()->json(['success' => true, 'message' => 'Ajuste eliminado. Se usará el precio calculado.']);
        }

        $valuation->update([
            'price_override'       => $request->input('price_override') ?: null,
            'price_override_notes' => $request->input('price_override_notes') ?: null,
        ]);

        if ($action === 'authorize') {
            $valuation->update([
                'price_override_authorized' => true,
                'price_override_by'         => auth()->id(),
                'price_override_at'         => now(),
            ]);

            return response()->json([
                'success'      => true,
                'authorized'   => true,
                'message'      => 'Precio autorizado para presentación.',
                'authorized_by'=> auth()->user()->name,
                'authorized_at'=> $valuation->price_override_at->format('d M Y H:i'),
                'final_price'  => number_format((int) $request->input('price_override'), 0, '.', ','),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Ajuste guardado.']);
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
