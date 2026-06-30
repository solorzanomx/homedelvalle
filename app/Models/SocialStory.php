<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialStory extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'media_type',
        'source_type',
        'source_id',
        'headline',
        'caption',
        'sticker_hashtags',
        'sticker_location',
        'sticker_link',
        'background_image_path',
        'rendered_image_path',
        'render_status',
        'render_error',
        'status',
        'scheduled_at',
        'published_at',
        'platform_story_id',
        'platform_story_url',
    ];

    protected $casts = [
        'sticker_hashtags' => 'array',
        'scheduled_at'     => 'datetime',
        'published_at'     => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            'instagram' => 'Instagram',
            'facebook'  => 'Facebook',
            default     => ucfirst($this->platform),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Borrador',
            'scheduled' => 'Programada',
            'published' => 'Publicada',
            'failed'    => 'Fallida',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'yellow',
            'scheduled' => 'blue',
            'published' => 'green',
            'failed'    => 'red',
            default     => 'gray',
        };
    }

    public function getMediaTypeLabelAttribute(): string
    {
        return match ($this->media_type) {
            'image' => 'Imagen',
            'video' => 'Video',
            default => ucfirst($this->media_type),
        };
    }

    public function hashtagsString(): string
    {
        if (empty($this->sticker_hashtags)) {
            return '';
        }

        return implode(' ', array_map(
            fn($h) => str_starts_with($h, '#') ? $h : '#' . $h,
            $this->sticker_hashtags
        ));
    }
}
