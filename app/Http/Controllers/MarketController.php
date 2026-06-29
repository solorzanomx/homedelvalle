<?php

namespace App\Http\Controllers;

use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Models\MarketZone;
use App\Models\MarketZoneSnapshot;
use Illuminate\View\View;

class MarketController extends Controller
{
    // ─── /mercado ─────────────────────────────────────────────────────────────

    public function index(): View
    {
        $zones = MarketZone::published()
            ->with(['publishedColonias'])
            ->orderBy('sort_order')
            ->get()
            ->map(function (MarketZone $zone) {
                // Precio promedio: departamento seminuevo de venta (zona-level)
                $snap = MarketZoneSnapshot::latestForZone($zone->id, 'sale', 'apartment', 'mid');
                $zone->avg_price_m2     = $snap ? (int) $snap->price_m2_avg : null;
                $zone->snap_confidence  = $snap?->confidence;
                $zone->snap_listings    = $snap?->sample_size;
                $zone->snap_period      = $snap?->period;
                return $zone;
            });

        return view('public.mercado.index', compact('zones'));
    }

    // ─── /mercado/{zona} ──────────────────────────────────────────────────────

    public function zone(string $zoneSlug): View
    {
        $zone     = MarketZone::where('slug', $zoneSlug)->where('is_published', true)->firstOrFail();
        $allZones = MarketZone::published()->orderBy('sort_order')->get();
        $colonias = $zone->publishedColonias()->orderBy('name')->get();

        // Leer zone snapshots — ya tienen operation_type correcto (no mezcla venta/renta)
        $zoneSnaps = MarketZoneSnapshot::summaryForZone($zone->id);

        // Filtrar solo medium/high confidence para el público
        $saleSnaps = $this->filterConfidence($zoneSnaps['sale'] ?? []);
        $rentSnaps = $this->filterConfidence($zoneSnaps['rent'] ?? []);

        // Stats de credibilidad
        $saleMeta = $this->snapMeta($saleSnaps);
        $rentMeta = $this->snapMeta($rentSnaps);

        // Precio hero (depto seminuevo venta)
        $heroPriceSale = ($saleSnaps['apartment']['mid'] ?? $saleSnaps['apartment']['new'] ?? null)?->price_m2_avg;

        // Datos históricos para gráficas (últimos 12 meses, depto seminuevo)
        $chartSale = MarketZoneSnapshot::chartDataForZone($zone->id, 'sale', 'apartment', 'mid', 12);
        $chartRent = MarketZoneSnapshot::chartDataForZone($zone->id, 'rent', 'apartment', 'mid', 12);

        // Validación por agente
        $isValidated = MarketZoneSnapshot::isZoneValidated($zone->id);
        $validatedBy = MarketZoneSnapshot::validatedBy($zone->id);

        // Related blog posts (for blog ↔ precios interconnection)
        $relatedPosts = \App\Models\Post::published()
            ->where(function ($q) use ($zone) {
                $q->where('title', 'like', '%' . $zone->name . '%')
                  ->orWhere('focus_keyword', 'like', '%' . strtolower($zone->name) . '%')
                  ->orWhere('zona_mercado_slug', $zone->slug);
            })
            ->latest('published_at')
            ->take(3)
            ->get(['id', 'title', 'slug', 'published_at']);

        return view('public.mercado.zone', compact(
            'zone', 'allZones', 'colonias',
            'saleSnaps', 'rentSnaps',
            'saleMeta', 'rentMeta',
            'heroPriceSale',
            'chartSale', 'chartRent',
            'isValidated', 'validatedBy',
            'relatedPosts',
        ));
    }

    // ─── /mercado/{zona}/{colonia} ────────────────────────────────────────────

    public function colonia(string $zoneSlug, string $coloniaSlug): View
    {
        $zone    = MarketZone::where('slug', $zoneSlug)->where('is_published', true)->firstOrFail();
        $colonia = MarketColonia::where('slug', $coloniaSlug)
            ->where('market_zone_id', $zone->id)
            ->where('is_published', true)
            ->firstOrFail();

        // Usa snapshots de zona como referencia (zone-level data)
        $allZones  = MarketZone::published()->orderBy('sort_order')->get();
        $zoneSnaps = MarketZoneSnapshot::summaryForZone($zone->id);
        $saleSnaps = $this->filterConfidence($zoneSnaps['sale'] ?? []);
        $rentSnaps = $this->filterConfidence($zoneSnaps['rent'] ?? []);
        $saleMeta  = $this->snapMeta($saleSnaps);
        $rentMeta  = $this->snapMeta($rentSnaps);

        $heroPriceSale = ($saleSnaps['apartment']['mid'] ?? $saleSnaps['apartment']['new'] ?? null)?->price_m2_avg;

        $siblings = $zone->publishedColonias()
            ->where('id', '!=', $colonia->id)
            ->orderBy('name')
            ->get();

        return view('public.mercado.colonia', compact(
            'zone', 'colonia', 'siblings', 'allZones',
            'saleSnaps', 'rentSnaps',
            'saleMeta', 'rentMeta',
            'heroPriceSale',
        ));
    }

    // ─── /mercado/opinion-de-valor ────────────────────────────────────────────

    public function opinionForm(): View
    {
        $colonias = MarketColonia::published()->with('zone')->orderBy('name')->get()->groupBy('zone.name');
        return view('public.mercado.opinion', compact('colonias'));
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Filtra snapshots al mínimo de confianza para mostrar en público.
     * Retorna [property_type][age_category] = snapshot|null
     */
    private function filterConfidence(array $snaps, array $minConf = ['high', 'medium', 'low']): array
    {
        $filtered = [];
        foreach ($snaps as $type => $ages) {
            foreach ($ages as $age => $snap) {
                if ($snap && in_array($snap->confidence, $minConf)) {
                    $filtered[$type][$age] = $snap;
                }
            }
        }
        return $filtered;
    }

    /**
     * Metadata de un conjunto de snapshots: total listings, último período, confianza dominante.
     */
    private function snapMeta(array $snaps): array
    {
        $allSnaps   = collect($snaps)->flatten()->filter();
        $totalList  = $allSnaps->sum('sample_size');
        $lastPeriod = $allSnaps->sortByDesc('period')->first()?->period;
        $confCounts = $allSnaps->countBy('confidence');
        $dominantConf = $confCounts->sortByDesc(fn($v) => match($v) { 'high' => 3, 'medium' => 2, default => 1 })->keys()->first();

        return [
            'total_listings' => $totalList,
            'last_period'    => $lastPeriod,
            'confidence'     => $dominantConf,
            'has_data'       => $allSnaps->isNotEmpty(),
        ];
    }
}
