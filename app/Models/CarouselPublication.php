<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarouselPublication extends Model
{
    protected $fillable = [
        'carousel_post_id',
        'channel',
        'status',
        'payload',
        'webhook_url',
        'webhook_response',
        'error_message',
        'scheduled_at',
        'sent_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'          => 'array',
            'webhook_response' => 'array',
            'scheduled_at'     => 'datetime',
            'sent_at'          => 'datetime',
            'published_at'     => 'datetime',
        ];
    }

    public function carouselPost(): BelongsTo
    {
        return $this->belongsTo(CarouselPost::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pendiente',
            'sending'   => 'Enviando',
            'sent'      => 'Enviado',
            'published' => 'Publicado',
            'failed'    => 'Fallido',
            default     => $this->status,
        };
    }
}
