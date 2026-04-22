<?php

namespace App\Http\Controllers;

use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Models\MarketZone;
use Illuminate\View\View;

class MarketController extends Controller
{
    /** /mercado — Hub del Observatorio */
    public function index(): View
    {
        $zones = MarketZone::published()
            ->with(['publishedColonias'])
            ->get()
            ->map(function (MarketZone $zone) {
                // Precio promedio de departamento medio en la zona
                $coloniaIds = $zone->publishedColonias->pluck('id');
                $avgPrice = MarketPriceSnapshot::whereIn('market_colonia_id', $coloniaIds)
                    ->where('property_type', 'apartment')
                    ->where('age_category', 'mid')
                    ->latest('period')
                    ->avg('price_m2_avg');

                $zone->avg_price_m2 = $avgPrice ? (int) round($avgPrice) : null;
                return $zone;
            });

        return view('public.mercado.index', compact('zones'));
    }

    /** /mercado/{zona} — Página de sector */
    public function zone(string $zoneSlug): View
    {
        $zone     = MarketZone::where('slug', $zoneSlug)->where('is_published', true)->firstOrFail();
        $colonias = $zone->publishedColonias()->get();

        // Snapshots más recientes por colonia + tipo + antigüedad
        $coloniaIds = $colonias->pluck('id');
        $snapshots  = MarketPriceSnapshot::whereIn('market_colonia_id', $coloniaIds)
            ->whereIn('property_type', ['apartment', 'house'])
            ->latest('period')
            ->get()
            ->groupBy(fn($s) => $s->market_colonia_id . '_' . $s->property_type . '_' . $s->age_category)
            ->map(fn($group) => $group->first());

        // Precio promedio de la zona para hero
        $zoneAvg = MarketPriceSnapshot::whereIn('market_colonia_id', $coloniaIds)
            ->where('property_type', 'apartment')
            ->where('age_category', 'mid')
            ->latest('period')
            ->avg('price_m2_avg');

        $allZones = MarketZone::published()->get(); // para el nav entre zonas

        return view('public.mercado.zone', compact('zone', 'colonias', 'snapshots', 'zoneAvg', 'allZones'));
    }

    /** /mercado/{zona}/{colonia} — Página de colonia */
    public function colonia(string $zoneSlug, string $coloniaSlug): View
    {
        $zone    = MarketZone::where('slug', $zoneSlug)->where('is_published', true)->firstOrFail();
        $colonia = MarketColonia::where('slug', $coloniaSlug)
            ->where('market_zone_id', $zone->id)
            ->where('is_published', true)
            ->firstOrFail();

        $snapshots = MarketPriceSnapshot::where('market_colonia_id', $colonia->id)
            ->whereIn('property_type', ['apartment', 'house'])
            ->latest('period')
            ->get()
            ->groupBy(fn($s) => $s->property_type . '_' . $s->age_category)
            ->map(fn($group) => $group->first());

        // Colonias vecinas de la misma zona para el footer
        $siblings = $zone->publishedColonias()
            ->where('id', '!=', $colonia->id)
            ->get();

        return view('public.mercado.colonia', compact('zone', 'colonia', 'snapshots', 'siblings'));
    }

    /** /mercado/opinion-de-valor — Landing + formulario */
    public function opinionForm(): View
    {
        $colonias = MarketColonia::published()->with('zone')->orderBy('name')->get()->groupBy('zone.name');

        return view('public.mercado.opinion', compact('colonias'));
    }
}
