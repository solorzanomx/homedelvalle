<?php

namespace App\Jobs;

use App\Models\MarketUpdateRun;
use App\Models\MarketZone;
use App\Models\MarketZoneSnapshot;
use App\Services\Market\PerplexityMarketService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateZonePricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    /**
     * @param string[] $propertyTypes  e.g. ['apartment','house'] or ['apartment','house','office']
     * @param string   $operationType  'sale' | 'rent'
     * @param int|null $runId          ID de MarketUpdateRun para tracking
     */
    public function __construct(
        public readonly MarketZone $zone,
        public readonly array      $propertyTypes  = ['apartment', 'house'],
        public readonly string     $operationType  = 'sale',
        public readonly ?int       $runId          = null,
    ) {}

    public function handle(PerplexityMarketService $service): void
    {
        if ($this->runId) {
            MarketUpdateRun::where('id', $this->runId)->update(['status' => 'running']);
        }

        $period = Carbon::now()->startOfMonth()->toDateString();

        foreach ($this->propertyTypes as $type) {
            $prices = $this->operationType === 'rent'
                ? $service->fetchZoneRentalPrices($this->zone, $type)
                : $service->fetchZonePrices($this->zone, $type);

            if (empty($prices)) {
                Log::error('UpdateZonePricesJob: sin datos', [
                    'zona'          => $this->zone->name,
                    'property_type' => $type,
                    'operation'     => $this->operationType,
                ]);
                continue;
            }

            foreach ($prices as $ageCategory => $range) {
                if ($ageCategory === '_meta') continue;

                $meta = $prices['_meta'] ?? [];

                MarketZoneSnapshot::updateOrCreate(
                    [
                        'market_zone_id' => $this->zone->id,
                        'operation_type' => $this->operationType,
                        'property_type'  => $type,
                        'age_category'   => $ageCategory,
                        'period'         => $period,
                    ],
                    [
                        'price_m2_low'   => $range['low'],
                        'price_m2_avg'   => $range['avg'],
                        'price_m2_high'  => $range['high'],
                        'sample_size'    => $meta['listings_analyzed'] ?? 0,
                        'listings_found' => $meta['listings_analyzed'] ?? 0,
                        'confidence'     => $meta['confidence'] ?? 'low',
                        'source'         => 'perplexity',
                        'source_raw'     => $meta['raw_listings'] ?? null,
                        'notes'          => trim(($meta['reasoning'] ?? '') . "\n\n" . ($meta['market_context'] ?? '')),
                    ]
                );
            }

            Log::info('UpdateZonePricesJob: actualizado', [
                'zona'          => $this->zone->name,
                'operation'     => $this->operationType,
                'property_type' => $type,
                'categorias'    => array_keys($prices),
            ]);
        }

        if ($this->runId) {
            MarketUpdateRun::where('id', $this->runId)
                ->update(['status' => 'done', 'completed_at' => now()]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('UpdateZonePricesJob: falló', [
            'zona'  => $this->zone->name,
            'error' => $e->getMessage(),
        ]);

        if ($this->runId) {
            MarketUpdateRun::where('id', $this->runId)->update([
                'status'       => 'failed',
                'error_msg'    => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
