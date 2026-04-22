<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketComparable extends Model
{
    protected $fillable = [
        'market_colonia_id', 'property_type',
        'address_hint', 'm2_total', 'm2_construction',
        'bedrooms', 'bathrooms', 'parking', 'age_years', 'floor',
        'list_price', 'sale_price', 'price_m2',
        'transaction_date', 'source', 'source_url', 'is_verified',
    ];

    protected $casts = [
        'list_price'       => 'decimal:2',
        'sale_price'       => 'decimal:2',
        'price_m2'         => 'decimal:2',
        'transaction_date' => 'date',
        'is_verified'      => 'boolean',
    ];

    public function colonia(): BelongsTo
    {
        return $this->belongsTo(MarketColonia::class, 'market_colonia_id');
    }
}
