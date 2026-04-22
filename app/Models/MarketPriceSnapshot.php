<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPriceSnapshot extends Model
{
    protected $fillable = [
        'market_colonia_id', 'property_type', 'age_category', 'period',
        'price_m2_low', 'price_m2_avg', 'price_m2_high',
        'sample_size', 'confidence', 'source', 'source_raw', 'notes', 'created_by',
    ];

    protected $casts = [
        'period'       => 'date',
        'price_m2_low' => 'decimal:2',
        'price_m2_avg' => 'decimal:2',
        'price_m2_high'=> 'decimal:2',
    ];

    public function colonia(): BelongsTo
    {
        return $this->belongsTo(MarketColonia::class, 'market_colonia_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getConfidenceLabelAttribute(): string
    {
        return match($this->confidence) {
            'high'   => 'Alta',
            'medium' => 'Media',
            default  => 'Baja',
        };
    }

    public function getConfidenceColorAttribute(): string
    {
        return match($this->confidence) {
            'high'   => 'green',
            'medium' => 'yellow',
            default  => 'red',
        };
    }

    public function getAgeLabelAttribute(): string
    {
        return match($this->age_category) {
            'new' => 'Nuevo (0-10 años)',
            'mid' => 'Seminuevo (10-30 años)',
            'old' => 'Antiguo (30+ años)',
            default => $this->age_category,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->property_type) {
            'apartment' => 'Departamento',
            'house'     => 'Casa',
            'land'      => 'Terreno',
            'office'    => 'Oficina',
            default     => $this->property_type,
        };
    }
}
