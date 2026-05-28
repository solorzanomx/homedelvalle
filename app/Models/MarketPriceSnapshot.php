<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPriceSnapshot extends Model
{
    protected $fillable = [
        'market_colonia_id', 'operation_type', 'property_type', 'age_category', 'period',
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
            'office'    => 'Oficina / Local Comercial',
            default     => $this->property_type,
        };
    }

    public function getOperationLabelAttribute(): string
    {
        return match($this->operation_type) {
            'rent' => 'Renta',
            default => 'Venta',
        };
    }

    public function isRent(): bool
    {
        return $this->operation_type === 'rent';
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeForSale($query)
    {
        return $query->where('operation_type', 'sale');
    }

    public function scopeForRent($query)
    {
        return $query->where('operation_type', 'rent');
    }

    /**
     * Devuelve el snapshot más reciente para una colonia + tipo de operación + tipo de inmueble.
     * Para renta residencial: toma el age_category 'mid' como referencia representativa.
     * Para renta comercial (office): ídem.
     */
    public static function latestForColonia(
        int    $coloniaId,
        string $operationType,
        string $propertyType,
        string $ageCategory = 'mid'
    ): ?self {
        return static::where('market_colonia_id', $coloniaId)
            ->where('operation_type', $operationType)
            ->where('property_type', $propertyType)
            ->where('age_category', $ageCategory)
            ->orderByDesc('period')
            ->first();
    }
}
