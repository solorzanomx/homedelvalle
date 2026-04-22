<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyValuation extends Model
{
    protected $fillable = [
        'property_id', 'created_by',
        'input_colonia_id', 'input_colonia_raw', 'input_type',
        'input_m2_total', 'input_m2_const', 'input_age_years',
        'input_condition', 'input_bedrooms', 'input_bathrooms', 'input_parking',
        'input_floor', 'input_has_elevator', 'input_has_rooftop',
        'input_has_balcony', 'input_has_service_room', 'input_has_storage',
        'input_unit_position', 'input_orientation', 'input_seismic_status',
        'input_notes',
        'base_price_m2', 'adjusted_price_m2',
        'total_value_low', 'total_value_mid', 'total_value_high', 'suggested_list_price',
        'market_trend', 'diagnosis', 'confidence',
        'snapshot_id', 'used_perplexity', 'perplexity_query', 'perplexity_response',
        'status', 'delivered_at', 'pdf_path',
        'actual_sale_price', 'accuracy_pct', 'sale_recorded_at',
        'ai_narrative',
    ];

    protected $casts = [
        'input_has_elevator'    => 'boolean',
        'input_has_rooftop'     => 'boolean',
        'input_has_balcony'     => 'boolean',
        'input_has_service_room'=> 'boolean',
        'input_has_storage'     => 'boolean',
        'used_perplexity'       => 'boolean',
        'base_price_m2'         => 'decimal:2',
        'adjusted_price_m2'     => 'decimal:2',
        'delivered_at'          => 'datetime',
        'sale_recorded_at'      => 'datetime',
        'ai_narrative'          => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function colonia(): BelongsTo
    {
        return $this->belongsTo(MarketColonia::class, 'input_colonia_id');
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(MarketPriceSnapshot::class, 'snapshot_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(ValuationAdjustment::class, 'valuation_id')->orderBy('sort_order');
    }

    public function comparables(): HasMany
    {
        return $this->hasMany(ValuationComparable::class, 'valuation_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return match($this->input_type) {
            'apartment' => 'Departamento',
            'house'     => 'Casa',
            'land'      => 'Terreno',
            'office'    => 'Oficina',
            default     => $this->input_type,
        };
    }

    public function getConditionLabelAttribute(): string
    {
        return match($this->input_condition) {
            'excellent' => 'Excelente / Remodelado',
            'good'      => 'Bueno',
            'fair'      => 'Regular',
            'poor'      => 'Necesita remodelación',
            default     => $this->input_condition,
        };
    }

    public function getDiagnosisLabelAttribute(): string
    {
        return match($this->diagnosis) {
            'on_market'        => 'En línea con el mercado',
            'above_market'     => 'Arriba del mercado',
            'opportunity'      => 'Oportunidad (debajo del mercado)',
            'insufficient_data'=> 'Datos insuficientes',
            default            => '—',
        };
    }

    public function getDiagnosisColorAttribute(): string
    {
        return match($this->diagnosis) {
            'on_market'    => 'green',
            'above_market' => 'red',
            'opportunity'  => 'blue',
            default        => 'yellow',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'Borrador',
            'final'     => 'Finalizada',
            'delivered' => 'Entregada',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'final'     => 'blue',
            'delivered' => 'green',
            default     => 'yellow',
        };
    }

    /** M² de construcción, o m² total si no está disponible */
    public function getEffectiveM2Attribute(): float
    {
        return (float) ($this->input_m2_const ?? $this->input_m2_total);
    }

    public function getAgeCategoryAttribute(): string
    {
        $years = $this->input_age_years;
        if ($years <= 10)  return 'new';
        if ($years <= 30)  return 'mid';
        return 'old';
    }
}
