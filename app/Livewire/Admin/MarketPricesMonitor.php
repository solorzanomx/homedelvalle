<?php

namespace App\Livewire\Admin;

use App\Jobs\UpdateZonePricesJob;
use App\Models\MarketColonia;
use App\Models\MarketUpdateRun;
use App\Models\MarketZone;
use App\Models\MarketZoneSnapshot;
use Livewire\Component;

class MarketPricesMonitor extends Component
{
    // ─── Despachar actualización de zona ────────────────────────────────────

    public function runUpdate(int $zoneId, string $operationType): void
    {
        $zone = MarketZone::findOrFail($zoneId);

        $saleTypes = ['apartment', 'house'];
        $rentTypes = ['apartment', 'house', 'office'];

        if (in_array($operationType, ['sale', 'both'])) {
            $run = MarketUpdateRun::create([
                'market_colonia_id' => null,
                'market_zone_id'    => $zone->id,
                'operation_type'    => 'sale',
                'property_types'    => $saleTypes,
                'status'            => 'pending',
                'dispatched_at'     => now(),
            ]);
            UpdateZonePricesJob::dispatch($zone, $saleTypes, 'sale', $run->id)
                ->onQueue('default');
        }
        if (in_array($operationType, ['rent', 'both'])) {
            $run = MarketUpdateRun::create([
                'market_colonia_id' => null,
                'market_zone_id'    => $zone->id,
                'operation_type'    => 'rent',
                'property_types'    => $rentTypes,
                'status'            => 'pending',
                'dispatched_at'     => now(),
            ]);
            UpdateZonePricesJob::dispatch($zone, $rentTypes, 'rent', $run->id)
                ->onQueue('default');
        }
    }

    public function runAll(string $operationType): void
    {
        $zones = MarketZone::orderBy('sort_order')->get();
        $delay = 0;

        $saleTypes = ['apartment', 'house'];
        $rentTypes = ['apartment', 'house', 'office'];

        foreach ($zones as $zone) {
            if (in_array($operationType, ['sale', 'both'])) {
                $run = MarketUpdateRun::create([
                    'market_colonia_id' => null,
                    'market_zone_id'    => $zone->id,
                    'operation_type'    => 'sale',
                    'property_types'    => $saleTypes,
                    'status'            => 'pending',
                    'dispatched_at'     => now(),
                ]);
                UpdateZonePricesJob::dispatch($zone, $saleTypes, 'sale', $run->id)
                    ->onQueue('default')
                    ->delay(now()->addSeconds($delay));
                $delay += 10;
            }
            if (in_array($operationType, ['rent', 'both'])) {
                $run = MarketUpdateRun::create([
                    'market_colonia_id' => null,
                    'market_zone_id'    => $zone->id,
                    'operation_type'    => 'rent',
                    'property_types'    => $rentTypes,
                    'status'            => 'pending',
                    'dispatched_at'     => now(),
                ]);
                UpdateZonePricesJob::dispatch($zone, $rentTypes, 'rent', $run->id)
                    ->onQueue('default')
                    ->delay(now()->addSeconds($delay));
                $delay += 10;
            }
        }
    }

    // ─── Render ─────────────────────────────────────────────────────────────

    public function render()
    {
        $zones = MarketZone::with('colonias')
            ->orderBy('sort_order')
            ->get();

        // Snapshots de zona agrupados por zone_id
        $allSnapshots = MarketZoneSnapshot::orderByDesc('period')->get()
            ->groupBy('market_zone_id');

        // Runs recientes (últimos 30 min), último por zona+tipo
        $recentRuns = MarketUpdateRun::where('created_at', '>', now()->subMinutes(60))
            ->whereNotNull('market_zone_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn($r) => $r->market_zone_id . '_' . $r->operation_type)
            ->map(fn($g) => $g->first());

        $hasActiveJobs = $recentRuns->contains(fn($r) => $r->isActive());
        $totalRuns     = $recentRuns->count();
        $doneRuns      = $recentRuns->where('status', 'done')->count();
        $failedRuns    = $recentRuns->where('status', 'failed')->count();
        $pendingRuns   = $recentRuns->filter(fn($r) => $r->isActive())->count();

        $lastPeriod    = MarketZoneSnapshot::max('period');

        return view('livewire.admin.market-prices-monitor', compact(
            'zones', 'allSnapshots', 'recentRuns',
            'hasActiveJobs', 'totalRuns', 'doneRuns', 'failedRuns', 'pendingRuns',
            'lastPeriod',
        ));
    }
}
