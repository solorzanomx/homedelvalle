<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateColoniaPricesJob;
use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketPricesController extends Controller
{
    public function index(): View
    {
        $colonias = MarketColonia::published()
            ->with(['zone', 'snapshots' => function ($q) {
                $q->orderBy('period', 'desc');
            }])
            ->orderBy('name')
            ->get();

        $lastPeriod = MarketPriceSnapshot::max('period');

        return view('admin.market.prices', compact('colonias', 'lastPeriod'));
    }

    /** Dispatch jobs for one colonia or all */
    public function run(Request $request): RedirectResponse
    {
        $coloniaId = $request->input('colonia_id');
        $types     = ['apartment', 'house'];

        if ($coloniaId === 'all') {
            $colonias = MarketColonia::published()->get();
        } else {
            $colonias = MarketColonia::published()->where('id', $coloniaId)->get();
        }

        if ($colonias->isEmpty()) {
            return back()->with('error', 'No se encontraron colonias publicadas.');
        }

        foreach ($colonias as $i => $colonia) {
            UpdateColoniaPricesJob::dispatch($colonia, $types)
                ->onQueue('default')
                ->delay(now()->addSeconds($i * 4)); // stagger 4s between requests
        }

        $label = $coloniaId === 'all'
            ? "las {$colonias->count()} colonias"
            : $colonias->first()->name;

        return back()->with('success', "Actualización de precios iniciada para {$label}. Los resultados aparecerán en 1-3 minutos por colonia.");
    }
}
