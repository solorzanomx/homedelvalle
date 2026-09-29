<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuccessionCalculatorConfig;
use Illuminate\Http\Request;

class SuccessionCalculatorConfigController extends Controller
{
    public function index()
    {
        $configs = SuccessionCalculatorConfig::orderByDesc('scenario')->get();   // con_testamento antes que sin_testamento

        return view('admin.succession-calculator.index', compact('configs'));
    }

    public function update(Request $request, SuccessionCalculatorConfig $successionCalculatorConfig)
    {
        $validated = $request->validate([
            'notarial_pct_min' => 'required|numeric|min:0|max:100',
            'notarial_pct_max' => 'required|numeric|min:0|max:100|gte:notarial_pct_min',
            'isai_pct_min' => 'required|numeric|min:0|max:100',
            'isai_pct_max' => 'required|numeric|min:0|max:100|gte:isai_pct_min',
            'registro_flat_min' => 'required|integer|min:0',
            'registro_flat_max' => 'required|integer|min:0|gte:registro_flat_min',
            'avaluo_flat_min' => 'required|integer|min:0',
            'avaluo_flat_max' => 'required|integer|min:0|gte:avaluo_flat_min',
            'otros_flat_min' => 'required|integer|min:0',
            'otros_flat_max' => 'required|integer|min:0|gte:otros_flat_min',
            'extra_heredero_flat' => 'required|integer|min:0',
            'sin_escrituras_extra_min' => 'required|integer|min:0',
            'sin_escrituras_extra_max' => 'required|integer|min:0|gte:sin_escrituras_extra_min',
            'tiempo_min_meses' => 'required|integer|min:1|max:60',
            'tiempo_max_meses' => 'required|integer|min:1|max:60|gte:tiempo_min_meses',
            'validated' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        $validated['validated'] = $request->boolean('validated');

        $successionCalculatorConfig->update($validated);

        return back()->with('success', 'Parámetros de "' . $successionCalculatorConfig->scenario . '" actualizados.');
    }
}
