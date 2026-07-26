<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\ConstructorValuationService;
use Illuminate\Http\Request;

class PropertyDevelopmentProfileController extends Controller
{
    public function edit(Property $property)
    {
        $profile = $property->developmentProfile;
        $zonificaciones = (new ConstructorValuationService())->getZonificaciones();

        return view('properties.development-profile', compact('property', 'profile', 'zonificaciones'));
    }

    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'frente' => 'nullable|numeric|min:0',
            'fondo' => 'nullable|numeric|min:0',
            'forma_terreno' => 'nullable|string|max:50',
            'uso_suelo' => 'nullable|string|max:100',
            'zonificacion_key' => 'nullable|string|max:50',
            'cos' => 'nullable|numeric|min:0|max:100',
            'cus' => 'nullable|numeric|min:0|max:100',
            'niveles_permitidos' => 'nullable|integer|min:0|max:255',
            'restricciones' => 'nullable|string',
            'colindancias' => 'nullable|string',
            'servicios' => 'nullable|string',
            'libre_gravamen' => 'nullable|in:0,1',
            'situacion_legal' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Tres estados reales (sí / no / sin verificar) — un select con value=""
        // para "sin verificar" no pasa por 'boolean', se normaliza aquí.
        $validated['libre_gravamen'] = $request->filled('libre_gravamen') ? $request->boolean('libre_gravamen') : null;

        // Si eligió una zonificación conocida y no llenó COS/CUS/niveles a mano,
        // se precargan desde el catálogo — puede sobreescribirlos si el predio
        // tiene alguna variante particular.
        if (!empty($validated['zonificacion_key'])) {
            $catalogo = ConstructorValuationService::ZONIFICACIONES[$validated['zonificacion_key']] ?? null;
            if ($catalogo) {
                $validated['cos'] = $validated['cos'] ?? $catalogo['cos'];
                $validated['cus'] = $validated['cus'] ?? $catalogo['cus'];
                $validated['niveles_permitidos'] = $validated['niveles_permitidos'] ?? $catalogo['pisos'];
            }
        }

        $property->developmentProfile()->updateOrCreate(
            ['property_id' => $property->id],
            $validated
        );

        return redirect()->route('properties.show', $property)->with('success', 'Perfil técnico actualizado.');
    }
}
