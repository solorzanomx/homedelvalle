<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketColonia extends Model
{
    protected $fillable = [
        'market_zone_id', 'name', 'slug', 'alcaldia', 'cp', 'is_published',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(MarketZone::class, 'market_zone_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(MarketPriceSnapshot::class)->latest('period');
    }

    public function comparables(): HasMany
    {
        return $this->hasMany(MarketComparable::class);
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(PropertyValuation::class, 'input_colonia_id');
    }

    /** Último snapshot disponible por tipo + categoría de antigüedad */
    public function latestSnapshot(string $type = 'apartment', string $ageCategory = 'mid'): ?MarketPriceSnapshot
    {
        return $this->snapshots()
            ->where('property_type', $type)
            ->where('age_category', $ageCategory)
            ->first();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('name');
    }
}
