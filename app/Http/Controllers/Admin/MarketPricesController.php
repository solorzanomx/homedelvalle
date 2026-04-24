<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateColoniaPricesJob;
use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Models\MarketZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketPricesController extends Controller
{
    public function index(): View
    {
        $zones = MarketZone::with(['colonias' => function ($q) {
                $q->with(['snapshots' => function ($sq) {
                    $sq->orderBy('period', 'desc');
                }])->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        $lastPeriod      = MarketPriceSnapshot::max('period');
        $totalColonias   = MarketColonia::count();
        $activeColonias  = MarketColonia::where('is_published', true)->count();

        return view('admin.market.prices', compact('zones', 'lastPeriod', 'totalColonias', 'activeColonias'));
    }

    /** Flip is_published for a single colonia */
    public function toggle(MarketColonia $colonia): RedirectResponse
    {
        $colonia->update(['is_published' => !$colonia->is_published]);

        $state = $colonia->is_published ? 'activada' : 'desactivada';

        return back()->with('success', "{$colonia->name} {$state} en el sitio.");
    }

    /** Dispatch jobs for one colonia or all published */
    public function run(Request $request): RedirectResponse
    {
        $coloniaId = $request->input('colonia_id');
        $types     = ['apartment', 'house'];

        if ($coloniaId === 'all') {
            $colonias = MarketColonia::published()->get();
        } else {
            $colonias = MarketColonia::where('id', $coloniaId)->get();
        }

        if ($colonias->isEmpty()) {
            return back()->with('error', 'No se encontraron colonias.');
        }

        foreach ($colonias as $i => $colonia) {
            UpdateColoniaPricesJob::dispatch($colonia, $types)
                ->onQueue('default')
                ->delay(now()->addSeconds($i * 4));
        }

        $label = $coloniaId === 'all'
            ? "las {$colonias->count()} colonias activas"
            : $colonias->first()->name;

        return back()->with('success', "Actualización de precios iniciada para {$label}. Los resultados aparecerán en 1–3 min por colonia.");
    }
}
