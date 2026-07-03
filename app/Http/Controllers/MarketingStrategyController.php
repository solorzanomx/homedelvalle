<?php

namespace App\Http\Controllers;

use App\Models\Operation;
use App\Models\PropertyMarketingStrategy;
use App\Services\Marketing\PropertyMarketingStrategyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingStrategyController extends Controller
{
    public function generate(Operation $operation, PropertyMarketingStrategyService $service)
    {
        $strategy = $service->generate($operation);

        if (!$strategy) {
            return back()->with('error', 'No se pudo generar la estrategia. Intenta de nuevo en un momento.');
        }

        return back()->with('success', 'Estrategia de promoción generada. Revísala antes de aprobarla.');
    }

    public function update(Request $request, Operation $operation, PropertyMarketingStrategy $marketingStrategy)
    {
        abort_unless($marketingStrategy->operation_id === $operation->id, 404);

        $validated = $request->validate([
            'perfil'             => 'nullable|string|max:500',
            'edad_rango'         => 'nullable|string|max:60',
            'ingresos_estimado'  => 'nullable|string|max:120',
            'intereses'          => 'nullable|string',
            'positioning_summary'=> 'nullable|string|max:1000',
            'recommended_channels'=> 'nullable|string',
            'key_selling_points' => 'nullable|string',
        ]);

        $toLines = fn (?string $text) => collect(explode("\n", (string) $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $marketingStrategy->update([
            'target_audience' => [
                'perfil'            => $validated['perfil'] ?? null,
                'edad_rango'        => $validated['edad_rango'] ?? null,
                'ingresos_estimado' => $validated['ingresos_estimado'] ?? null,
                'intereses'         => $toLines($validated['intereses'] ?? null),
            ],
            'positioning_summary'   => $validated['positioning_summary'] ?? null,
            'recommended_channels' => $toLines($validated['recommended_channels'] ?? null),
            'key_selling_points'    => $toLines($validated['key_selling_points'] ?? null),
            // Editar a mano invalida la aprobación previa — el broker debe
            // revisar y volver a aprobar antes de que cuente como definitiva.
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Estrategia actualizada.');
    }

    public function approve(Operation $operation, PropertyMarketingStrategy $marketingStrategy)
    {
        abort_unless($marketingStrategy->operation_id === $operation->id, 404);

        $marketingStrategy->update([
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Estrategia de promoción aprobada.');
    }
}
