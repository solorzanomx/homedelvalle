<?php

namespace App\Http\Controllers;

use App\Models\PolizaPlan;
use Illuminate\Http\Request;

/** Catálogo editable de planes de póliza (hoy Previsión Legal). Solo administradores. */
class PolizaPlanController extends Controller
{
    public function index()
    {
        return view('poliza-plans.index', ['plans' => PolizaPlan::orderBy('sort_order')->orderBy('id')->get()]);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'provider_name' => 'nullable|string|max:100',
            'tagline' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:600',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ];
    }

    private function payload(Request $request): array
    {
        $v = $request->validate($this->rules());
        return [
            'name' => $v['name'],
            'provider_name' => $v['provider_name'] ?: 'Previsión Legal',
            'tagline' => $v['tagline'] ?? null,
            'description' => $v['description'] ?? null,
            'sort_order' => $v['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'is_recommended' => $request->boolean('is_recommended'),
            'show_on_website' => $request->boolean('show_on_website'),
            'show_price_public' => $request->boolean('show_price_public'),
        ];
    }

    public function store(Request $request)
    {
        PolizaPlan::create($this->payload($request));

        return back()->with('success', 'Plan agregado.');
    }

    public function update(Request $request, PolizaPlan $plan)
    {
        $data = $this->payload($request);
        $plan->update($data);

        return back()->with('success', "Plan \"{$plan->name}\" guardado.");
    }

    public function destroy(PolizaPlan $plan)
    {
        if (\App\Models\RentalProcess::where('poliza_plan_id', $plan->id)->exists()) {
            return back()->with('error', 'Ese plan ya lo eligió algún inquilino: desactívalo en vez de borrarlo.');
        }
        $plan->delete();

        return back()->with('success', 'Plan eliminado.');
    }

    /** Tarifario (rangos de renta), gastos de emisión y matriz de cobertura de la hoja de servicios vigente. */
    public function tarifario()
    {
        $sheet = \App\Support\PolizaPricing::sheet();
        $plans = PolizaPlan::orderBy('sort_order')->orderBy('id')->get();
        $rates = $sheet ? $sheet->rates()->orderBy('sort_order')->get()->groupBy('sort_order') : collect();
        $coverages = \App\Models\PolizaCoverage::with('plans')->orderBy('sort_order')->get();

        return view('poliza-plans.tarifario', compact('sheet', 'plans', 'rates', 'coverages'));
    }

    public function saveTarifario(Request $request)
    {
        $sheet = \App\Support\PolizaPricing::sheet();
        abort_unless($sheet, 404);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'zone_label' => 'nullable|string|max:200',
            'valid_year' => 'nullable|integer|min:2020|max:2100',
            'emission_fee' => 'required|numeric|min:0|max:999999',
            'rates' => 'array',
            'rates.*.fixed_price' => 'nullable|numeric|min:0|max:99999999',
            'rates.*.percent' => 'nullable|numeric|min:0|max:100',
        ]);
        $sheet->update(collect($data)->only(['name', 'zone_label', 'valid_year', 'emission_fee'])->all());

        foreach ($data['rates'] ?? [] as $id => $vals) {
            \App\Models\PolizaRate::where('id', $id)->where('poliza_tariff_sheet_id', $sheet->id)->update([
                'fixed_price' => $vals['fixed_price'] ?? null,
                'percent' => $vals['percent'] ?? null,
            ]);
        }

        // Matriz de cobertura: una casilla por concepto × plan.
        $checked = $request->input('cov', []);
        foreach (\App\Models\PolizaCoverage::all() as $cov) {
            foreach (PolizaPlan::pluck('id') as $planId) {
                \Illuminate\Support\Facades\DB::table('poliza_coverage_plan')->updateOrInsert(
                    ['poliza_coverage_id' => $cov->id, 'poliza_plan_id' => $planId],
                    ['included' => ! empty($checked[$cov->id][$planId])]
                );
            }
        }

        return back()->with('success', 'Tarifario y cobertura guardados.');
    }
}
