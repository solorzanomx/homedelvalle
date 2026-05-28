<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketUpdateRun extends Model
{
    protected $fillable = [
        'market_colonia_id',
        'market_zone_id',
        'operation_type',
        'status',
        'property_types',
        'dispatched_at',
        'completed_at',
        'error_msg',
    ];

    protected $casts = [
        'property_types' => 'array',
        'dispatched_at'  => 'datetime',
        'completed_at'   => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function colonia(): BelongsTo
    {
        return $this->belongsTo(MarketColonia::class, 'market_colonia_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(MarketZone::class, 'market_zone_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** Jobs que aún no terminaron */
    public function scopeActive($q): void
    {
        $q->whereIn('status', ['pending', 'running']);
    }

    /** Runs en las últimas N minutos */
    public function scopeRecent($q, int $minutes = 30): void
    {
        $q->where('created_at', '>', now()->subMinutes($minutes));
    }

    // ─── Estado helpers ───────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'running']);
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /** True si terminó recientemente (para mostrar badge "Listo" durante 5 min) */
    public function isRecentlyDone(): bool
    {
        return $this->status === 'done'
            && $this->completed_at
            && $this->completed_at->gt(now()->subMinutes(5));
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En cola',
            'running' => 'Procesando...',
            'done'    => 'Listo',
            'failed'  => 'Error',
            default   => $this->status,
        };
    }

    public function getOpLabelAttribute(): string
    {
        return match($this->operation_type) {
            'sale' => 'Venta',
            'rent' => 'Renta',
            default => $this->operation_type,
        };
    }
}
