<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketZoneSnapshot extends Model
{
    protected $fillable = [
        'market_zone_id', 'operation_type', 'property_type', 'age_category', 'period',
        'price_m2_low', 'price_m2_avg', 'price_m2_high',
        'sample_size', 'listings_found', 'confidence',
        'source', 'source_raw', 'notes',
    ];

    protected $casts = [
        'period'        => 'date',
        'price_m2_low'  => 'decimal:2',
        'price_m2_avg'  => 'decimal:2',
        'price_m2_high' => 'decimal:2',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(MarketZone::class, 'market_zone_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForSale($q)   { $q->where('operation_type', 'sale'); }
    public function scopeForRent($q)   { $q->where('operation_type', 'rent'); }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Devuelve el snapshot más reciente de una zona para operation+property+age.
     */
    public static function latestForZone(
        int    $zoneId,
        string $operationType,
        string $propertyType,
        string $ageCategory = 'mid'
    ): ?self {
        return static::where('market_zone_id', $zoneId)
            ->where('operation_type',  $operationType)
            ->where('property_type',   $propertyType)
            ->where('age_category',    $ageCategory)
            ->orderByDesc('period')
            ->first();
    }

    /**
     * Todos los snapshots de una zona agrupados por property_type → age_category.
     * Retorna el más reciente por cada combinación.
     * Resultado: ['sale' => ['apartment' => ['new' => snap, 'mid' => snap, ...], ...], 'rent' => ...]
     */
    public static function summaryForZone(int $zoneId): array
    {
        $snaps = static::where('market_zone_id', $zoneId)
            ->orderByDesc('period')
            ->get();

        $result = [];
        foreach ($snaps as $snap) {
            $op   = $snap->operation_type;
            $prop = $snap->property_type;
            $age  = $snap->age_category;
            // Solo guarda el más reciente (ordenamos desc arriba)
            if (!isset($result[$op][$prop][$age])) {
                $result[$op][$prop][$age] = $snap;
            }
        }
        return $result;
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
            'high'   => '#16a34a',
            'medium' => '#d97706',
            default  => '#94a3b8',
        };
    }
}
