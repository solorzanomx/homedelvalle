<?php

namespace App\Jobs;

use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Services\Market\PerplexityMarketService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateColoniaPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    /** @param string[] $propertyTypes */
    public function __construct(
        public readonly MarketColonia $colonia,
        public readonly array $propertyTypes = ['apartment', 'house'],
    ) {}

    public function handle(PerplexityMarketService $service): void
    {
        $period = Carbon::now()->startOfMonth()->toDateString(); // e.g. 2026-04-01

        foreach ($this->propertyTypes as $type) {
            $prices = $service->fetchPrices($this->colonia, $type);

            if (empty($prices)) {
                Log::warning('UpdateColoniaPricesJob: sin datos de Perplexity', [
                    'colonia'       => $this->colonia->name,
                    'property_type' => $type,
                ]);
                continue;
            }

            foreach ($prices as $ageCategory => $range) {
                if ($ageCategory === '_meta') continue; // skip metadata key

                $meta = $prices['_meta'] ?? [];

                MarketPriceSnapshot::updateOrCreate(
                    [
                        'market_colonia_id' => $this->colonia->id,
                        'property_type'     => $type,
                        'age_category'      => $ageCategory,
                        'period'            => $period,
                    ],
                    [
                        'price_m2_low'  => $range['low'],
                        'price_m2_avg'  => $range['avg'],
                        'price_m2_high' => $range['high'],
                        'source'        => 'perplexity',
                        'sample_size'   => $meta['listings_analyzed'] ?? 0,
                        'confidence'    => $meta['confidence'] ?? 'low',
                        'source_raw'    => $meta['raw_listings'] ?? null,
                        'notes'         => ($meta['reasoning'] ?? '') . "\n\n" . ($meta['market_context'] ?? ''),
                    ]
                );
            }

            Log::info('UpdateColoniaPricesJob: actualizado', [
                'colonia'       => $this->colonia->name,
                'property_type' => $type,
                'categories'    => array_keys($prices),
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('UpdateColoniaPricesJob: falló', [
            'colonia' => $this->colonia->name,
            'error'   => $e->getMessage(),
        ]);
    }
}
