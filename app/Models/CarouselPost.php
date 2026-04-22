<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CarouselPost extends Model
{
    protected $fillable = [
        'title',
        'type',
        'source_type',
        'source_id',
        'template_id',
        'status',
        'caption_short',
        'caption_long',
        'hashtags',
        'cta',
        'ai_prompt_used',
        'user_id',
        'approved_by',
        'approved_version_id',
        'approved_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'hashtags'    => 'array',
            'approved_at' => 'datetime',
            'published_at'=> 'datetime',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function template(): BelongsTo
    {
        return $this->belongsTo(CarouselTemplate::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvedVersion(): BelongsTo
    {
        return $this->belongsTo(CarouselVersion::class, 'approved_version_id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(CarouselSlide::class)->orderBy('order');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(CarouselAsset::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CarouselVersion::class)->latest();
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(CarouselVersion::class)->latestOfMany('version_number');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(CarouselPublication::class)->latest();
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeReady($query)
    {
        return $query->whereIn('status', ['review', 'approved']);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'review']);
    }

    public function coverSlide(): ?CarouselSlide
    {
        return $this->slides->where('type', 'cover')->first()
            ?? $this->slides->first();
    }

    public function getHashtagsStringAttribute(): string
    {
        if (empty($this->hashtags)) {
            return '';
        }
        return implode(' ', array_map(
            fn($t) => str_starts_with($t, '#') ? $t : '#' . $t,
            $this->hashtags
        ));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'Borrador',
            'generating' => 'Generando',
            'review'     => 'En revisión',
            'approved'   => 'Aprobado',
            'published'  => 'Publicado',
            'archived'   => 'Archivado',
            default      => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'      => 'yellow',
            'generating' => 'blue',
            'review'     => 'orange',
            'approved'   => 'green',
            'published'  => 'teal',
            'archived'   => 'gray',
            default      => 'gray',
        };
    }
}
