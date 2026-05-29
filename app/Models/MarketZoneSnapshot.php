<?php

namespace App\Models;

use Carbon\Carbon;
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
     * Rolling average de los últimos N meses por zona.
     * Devuelve un snapshot "virtual" (no persistido) con los valores promediados.
     * Resultado: ['sale' => ['apartment' => ['new' => snap, 'mid' => snap, ...], ...], 'rent' => ...]
     */
    public static function summaryForZone(int $zoneId, int $rollingMonths = 3): array
    {
        $cutoff = now()->subMonths($rollingMonths)->startOfMonth();

        $snaps = static::where('market_zone_id', $zoneId)
            ->where('period', '>=', $cutoff)
            ->orderByDesc('period')
            ->get();

        // Agrupar por operación / tipo / antigüedad
        $groups = [];
        foreach ($snaps as $snap) {
            $key = "{$snap->operation_type}.{$snap->property_type}.{$snap->age_category}";
            $groups[$key]['snaps'][]     = $snap;
            $groups[$key]['op']          = $snap->operation_type;
            $groups[$key]['prop']        = $snap->property_type;
            $groups[$key]['age']         = $snap->age_category;
        }

        $confWeight = ['high' => 3, 'medium' => 2, 'low' => 1];
        $result     = [];

        foreach ($groups as $group) {
            $col = collect($group['snaps']);

            // Promediar precio
            $avgLow  = $col->avg(fn($s) => (float) $s->price_m2_low);
            $avgMid  = $col->avg(fn($s) => (float) $s->price_m2_avg);
            $avgHigh = $col->avg(fn($s) => (float) $s->price_m2_high);

            // Mejor confianza del período
            $bestConf = $col->sortByDesc(fn($s) => $confWeight[$s->confidence] ?? 0)
                            ->first()->confidence;

            // Sumar listings de todos los meses
            $totalSamples = $col->sum('sample_size');

            // Período más reciente para mostrar
            $latestPeriod = $col->sortByDesc('period')->first()->period;

            // Snapshot virtual (no se persiste)
            $virtual                  = new static();
            $virtual->price_m2_low    = round($avgLow);
            $virtual->price_m2_avg    = round($avgMid);
            $virtual->price_m2_high   = round($avgHigh);
            $virtual->sample_size     = $totalSamples;
            $virtual->confidence      = $bestConf;
            $virtual->period          = $latestPeriod;

            $result[$group['op']][$group['prop']][$group['age']] = $virtual;
        }

        return $result;
    }

    /**
     * Datos históricos para la gráfica de evolución de precios.
     * Devuelve array ordenado por período (el más viejo primero).
     */
    public static function chartDataForZone(
        int    $zoneId,
        string $operationType,
        string $propertyType,
        string $ageCategory = 'mid',
        int    $months      = 12
    ): array {
        $cutoff = now()->subMonths($months)->startOfMonth();

        return static::where('market_zone_id', $zoneId)
            ->where('operation_type', $operationType)
            ->where('property_type',  $propertyType)
            ->where('age_category',   $ageCategory)
            ->where('period', '>=', $cutoff)
            ->orderBy('period')
            ->get()
            ->map(fn($s) => [
                'period'     => $s->period->format('Y-m'),
                'label'      => ucfirst($s->period->translatedFormat('M Y')),
                'avg'        => (int) $s->price_m2_avg,
                'low'        => (int) $s->price_m2_low,
                'high'       => (int) $s->price_m2_high,
                'samples'    => (int) $s->sample_size,
                'confidence' => $s->confidence,
            ])
            ->values()
            ->toArray();
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

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
