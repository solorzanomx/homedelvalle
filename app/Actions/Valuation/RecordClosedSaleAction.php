<?php

namespace App\Actions\Valuation;

use App\Models\MarketComparable;
use App\Models\PropertyValuation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecordClosedSaleAction
{
    /**
     * Record an actual sale price against a valuation.
     *
     * - Computes accuracy_pct vs suggested_list_price
     * - Creates a MarketComparable with source='own' for future pricing
     * - Updates the valuation record
     */
    public function execute(PropertyValuation $valuation, float $salePrice, ?Carbon $closedAt = null): void
    {
        $closedAt ??= now();

        // accuracy: how far off was our prediction?
        // positive = sold above our suggested price, negative = below
        $accuracyPct = null;
        if ($valuation->suggested_list_price && $valuation->suggested_list_price > 0) {
            $accuracyPct = round(
                (($salePrice - $valuation->suggested_list_price) / $valuation->suggested_list_price) * 100,
                2
            );
        }

        $valuation->update([
            'actual_sale_price' => $salePrice,
            'accuracy_pct'      => $accuracyPct,
            'sale_recorded_at'  => $closedAt,
        ]);

        // Save as own comparable so it feeds future valuations
        $this->createComparable($valuation, $salePrice, $closedAt);

        Log::info('RecordClosedSaleAction: cierre registrado', [
            'valuation_id'      => $valuation->id,
            'sale_price'        => $salePrice,
            'suggested_price'   => $valuation->suggested_list_price,
            'accuracy_pct'      => $accuracyPct,
        ]);
    }

    private function createComparable(PropertyValuation $valuation, float $salePrice, Carbon $closedAt): void
    {
        if (!$valuation->input_colonia_id) {
            return;
        }

        $effectiveM2 = $valuation->effective_m2;
        if ($effectiveM2 <= 0) {
            return;
        }

        $priceM2 = round($salePrice / $effectiveM2, 2);

        MarketComparable::create([
            'market_colonia_id' => $valuation->input_colonia_id,
            'property_type'     => $valuation->input_type,
            'm2_total'          => $valuation->input_m2_total,
            'm2_construction'   => $valuation->input_m2_const,
            'bedrooms'          => $valuation->input_bedrooms,
            'bathrooms'         => $valuation->input_bathrooms,
            'parking'           => $valuation->input_parking,
            'age_years'         => $valuation->input_age_years,
            'floor'             => $valuation->input_floor,
            'list_price'        => $valuation->suggested_list_price,
            'sale_price'        => $salePrice,
            'price_m2'          => $priceM2,
            'transaction_date'  => $closedAt->toDateString(),
            'source'            => 'own',
            'is_verified'       => true,
        ]);
    }
}
