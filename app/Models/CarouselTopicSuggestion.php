<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarouselTopicSuggestion extends Model
{
    protected $fillable = [
        'session_id', 'source', 'title', 'description', 'reasoning',
        'suggested_type', 'suggested_keywords', 'relevance_score', 'priority',
        'status', 'converted_carousel_id', 'created_by',
    ];

    protected $casts = [
        'suggested_keywords' => 'array',
        'relevance_score'    => 'integer',
        'priority'           => 'integer',
    ];

    /* ── Relationships ── */

    public function convertedCarousel(): BelongsTo
    {
        return $this->belongsTo(CarouselPost::class, 'converted_carousel_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ── Scopes ── */

    public function scopePending($q)   { return $q->where('status', 'pending'); }
    public function scopeSelected($q)  { return $q->where('status', 'selected'); }
    public function scopeConverted($q) { return $q->where('status', 'converted'); }

    /* ── Helpers ── */

    public function getRelevanceLabelAttribute(): string
    {
        return match (true) {
            $this->relevance_score >= 80 => 'high',
            $this->relevance_score >= 60 => 'medium',
            default                      => 'low',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->suggested_type) {
            'commercial'  => 'blue',
            'educational' => 'green',
            'capture'     => 'purple',
            'informative' => 'yellow',
            'branding'    => 'pink',
            default       => 'gray',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->suggested_type) {
            'commercial'  => 'Comercial',
            'educational' => 'Educativo',
            'capture'     => 'Captación',
            'informative' => 'Informativo',
            'branding'    => 'Branding',
            default       => ucfirst($this->suggested_type),
        };
    }
}
