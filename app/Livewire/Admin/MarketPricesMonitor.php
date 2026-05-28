<?php

namespace App\Livewire\Admin;

use App\Jobs\UpdateColoniaPricesJob;
use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Models\MarketUpdateRun;
use App\Models\MarketZone;
use Livewire\Component;

class MarketPricesMonitor extends Component
{
    // ─── Datos de la última actualización masiva para el banner ──────────────
    public int    $batchTotal     = 0;
    public int    $batchDone      = 0;
    public int    $batchFailed    = 0;
    public string $batchStartedAt = '';   // ISO timestamp del inicio del batch activo

    // ─── Despachar actualización ─────────────────────────────────────────────

    public function runUpdate(string $coloniaId, string $operationType): void
    {
        $saleTypes = ['apartment', 'house'];
        $rentTypes = ['apartment', 'house', 'office'];

        if ($coloniaId === 'all') {
            $colonias = MarketColonia::where('is_published', true)->get();
        } else {
            $colonias = MarketColonia::where('id', (int) $coloniaId)->get();
        }

        if ($colonias->isEmpty()) {
            session()->flash('error', 'No se encontraron colonias activas.');
            return;
        }

        $delay = 0;
        foreach ($colonias as $colonia) {
            if (in_array($operationType, ['sale', 'both'])) {
                $run = MarketUpdateRun::create([
                    'market_colonia_id' => $colonia->id,
                    'operation_type'    => 'sale',
                    'property_types'    => $saleTypes,
                    'status'            => 'pending',
                    'dispatched_at'     => now(),
                ]);
                UpdateColoniaPricesJob::dispatch($colonia, $saleTypes, 'sale', $run->id)
                    ->onQueue('default')
                    ->delay(now()->addSeconds($delay));
                $delay += 5;
            }
            if (in_array($operationType, ['rent', 'both'])) {
                $run = MarketUpdateRun::create([
                    'market_colonia_id' => $colonia->id,
                    'operation_type'    => 'rent',
                    'property_types'    => $rentTypes,
                    'status'            => 'pending',
                    'dispatched_at'     => now(),
                ]);
                UpdateColoniaPricesJob::dispatch($colonia, $rentTypes, 'rent', $run->id)
                    ->onQueue('default')
                    ->delay(now()->addSeconds($delay));
                $delay += 5;
            }
        }

        $this->batchStartedAt = now()->toISOString();
    }

    // ─── Toggle publicación de colonia ────────────────────────────────────────

    public function toggleColonia(int $coloniaId): void
    {
        $colonia = MarketColonia::findOrFail($coloniaId);
        $colonia->update(['is_published' => !$colonia->is_published]);
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        // Zonas con colonias y sus snapshots
        $zones = MarketZone::with(['colonias' => function ($q) {
                $q->with(['snapshots' => function ($sq) {
                    $sq->orderBy('period', 'desc');
                }])->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get();

        // Runs recientes (últimos 30 min), último por colonia+tipo
        $recentRuns = MarketUpdateRun::with('colonia')
            ->where('created_at', '>', now()->subMinutes(30))
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy(fn($r) => $r->market_colonia_id . '_' . $r->operation_type)
            ->map(fn($group) => $group->first());   // ya están ordenados por id desc

        // Stats para banner de progreso
        $allActiveRuns = $recentRuns->values();
        $hasActiveJobs = $allActiveRuns->contains(fn($r) => $r->isActive());
        $totalRuns     = $allActiveRuns->count();
        $doneRuns      = $allActiveRuns->where('status', 'done')->count();
        $failedRuns    = $allActiveRuns->where('status', 'failed')->count();
        $pendingRuns   = $allActiveRuns->filter(fn($r) => $r->isActive())->count();

        $lastPeriod     = MarketPriceSnapshot::max('period');
        $totalColonias  = MarketColonia::count();
        $activeColonias = MarketColonia::where('is_published', true)->count();

        return view('livewire.admin.market-prices-monitor', compact(
            'zones', 'recentRuns',
            'hasActiveJobs', 'totalRuns', 'doneRuns', 'failedRuns', 'pendingRuns',
            'lastPeriod', 'totalColonias', 'activeColonias',
        ));
    }
}
