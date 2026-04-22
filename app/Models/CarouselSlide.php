<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarouselSlide extends Model
{
    protected $fillable = [
        'carousel_post_id',
        'order',
        'type',
        'headline',
        'subheadline',
        'body',
        'cta_text',
        'background_image_path',
        'secondary_image_path',
        'overlay_color',
        'overlay_opacity',
        'custom_data',
        'rendered_image_path',
        'render_status',
        'render_error',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'order'           => 'integer',
            'overlay_opacity' => 'integer',
            'custom_data'     => 'array',
            'is_locked'       => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function carouselPost(): BelongsTo
    {
        return $this->belongsTo(CarouselPost::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(CarouselAsset::class, 'slide_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isRendered(): bool
    {
        return $this->render_status === 'done' && $this->rendered_image_path !== null;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'cover'        => 'Portada',
            'problem'      => 'Problema',
            'key_stat'     => 'Estadística',
            'explanation'  => 'Explicación',
            'benefit'      => 'Beneficio',
            'example'      => 'Ejemplo',
            'social_proof' => 'Prueba Social',
            'cta'          => 'CTA',
            default        => ucfirst($this->type),
        };
    }
}
