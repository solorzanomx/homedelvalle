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
            'price' => 'nullable|numeric|min:0|max:9999999',
            'description' => 'nullable|string|max:600',
            'inclusions_text' => 'nullable|string|max:3000',
            'sort_order' => 'nullable|integer|min:0|max:999',
        ];
    }

    private function payload(Request $request): array
    {
        $v = $request->validate($this->rules());
        $inclusions = collect(preg_split('/\r\n|\r|\n/', (string) ($v['inclusions_text'] ?? '')))
            ->map(fn($l) => trim($l))->filter()->values()->all();

        return [
            'name' => $v['name'],
            'provider_name' => $v['provider_name'] ?: 'Previsión Legal',
            'tagline' => $v['tagline'] ?? null,
            'price' => $v['price'] ?? null,
            'description' => $v['description'] ?? null,
            'inclusions' => $inclusions,
            'sort_order' => $v['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
            'is_recommended' => $request->boolean('is_recommended'),
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
        if ($data['is_active'] && $data['price'] === null) {
            return back()->with('error', "El plan \"{$data['name']}\" necesita un precio para poder activarse (el Portal solo muestra planes con precio).");
        }
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
}
