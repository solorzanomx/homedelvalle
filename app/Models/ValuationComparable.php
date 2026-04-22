<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValuationComparable extends Model
{
    protected $fillable = [
        'valuation_id', 'comparable_id',
        'description', 'price_m2', 'distance_category', 'relevance_note',
    ];

    protected $casts = ['price_m2' => 'decimal:2'];

    public function valuation(): BelongsTo
    {
        return $this->belongsTo(PropertyValuation::class, 'valuation_id');
    }

    public function marketComparable(): BelongsTo
    {
        return $this->belongsTo(MarketComparable::class, 'comparable_id');
    }

    public function getDistanceLabelAttribute(): string
    {
        return match($this->distance_category) {
            'same_colonia' => 'Misma colonia',
            'adjacent'     => 'Colonia adyacente',
            'same_zone'    => 'Misma zona',
            default        => $this->distance_category,
        };
    }
}
