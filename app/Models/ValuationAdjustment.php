<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValuationAdjustment extends Model
{
    protected $fillable = [
        'valuation_id', 'sort_order', 'factor_key', 'factor_label',
        'adjustment_type', 'adjustment_value',
        'price_before', 'price_after', 'explanation',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:4',
        'price_before'     => 'decimal:2',
        'price_after'      => 'decimal:2',
    ];

    public function valuation(): BelongsTo
    {
        return $this->belongsTo(PropertyValuation::class, 'valuation_id');
    }

    /** Ajuste en porcentaje formateado: "+4.0%" o "-22.0%" */
    public function getFormattedValueAttribute(): string
    {
        if ($this->adjustment_type === 'percent') {
            $pct = $this->adjustment_value * 100;
            return ($pct >= 0 ? '+' : '') . number_format($pct, 1) . '%';
        }
        return ($this->adjustment_value >= 0 ? '+' : '') . '$' . number_format(abs($this->adjustment_value), 0);
    }

    /** true si es un ajuste positivo */
    public function getIsPositiveAttribute(): bool
    {
        return $this->adjustment_value > 0;
    }

    /** true si no hay cambio */
    public function getIsNeutralAttribute(): bool
    {
        return $this->adjustment_value == 0;
    }
}
