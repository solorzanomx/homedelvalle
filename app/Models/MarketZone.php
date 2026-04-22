<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketZone extends Model
{
    protected $fillable = [
        'slug', 'name', 'short_description', 'long_description',
        'lat_center', 'lng_center', 'sort_order', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'lat_center'   => 'decimal:7',
        'lng_center'   => 'decimal:7',
    ];

    public function colonias(): HasMany
    {
        return $this->hasMany(MarketColonia::class)->orderBy('name');
    }

    public function publishedColonias(): HasMany
    {
        return $this->colonias()->where('is_published', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }
}
