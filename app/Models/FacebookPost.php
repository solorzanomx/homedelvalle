<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookPost extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'source_type',
        'source_id',
        'template',
        'headline',
        'subheadline',
        'body_text',
        'caption',
        'hashtags',
        'background_image_path',
        'rendered_image_path',
        'render_status',
        'render_error',
        'status',
        'published_at',
    ];

    protected $casts = [
        'hashtags'     => 'array',
        'published_at' => 'datetime',
    ];

    public const TEMPLATES = [
        'fb-dark'     => 'Oscuro — navy elegante',
        'fb-light'    => 'Claro — blanco limpio',
        'fb-foto'     => 'Foto — imagen completa',
        'fb-gradient' => 'Degradado — azul HDV',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourcePost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'source_id');
    }

    public function hashtagsString(): string
    {
        if (empty($this->hashtags)) {
            return '';
        }

        return implode(' ', array_map(
            fn($h) => str_starts_with($h, '#') ? $h : '#' . $h,
            $this->hashtags
        ));
    }
}
