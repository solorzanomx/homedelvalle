<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresentationSend extends Model
{
    protected $fillable = [
        'captacion_id', 'channel', 'sent_by_user_id', 'recipient_email', 'recipient_phone',
        'tracking_token', 'sent_at', 'email_opened_at', 'link_clicked_at', 'pdf_viewed_at',
        'pdf_view_count', 'pdf_downloaded_at', 'last_view_ip', 'last_view_user_agent', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'            => 'datetime',
            'email_opened_at'    => 'datetime',
            'link_clicked_at'    => 'datetime',
            'pdf_viewed_at'      => 'datetime',
            'pdf_downloaded_at'  => 'datetime',
            'metadata'           => 'array',
        ];
    }

    const CHANNELS = [
        'email'     => 'Email',
        'whatsapp'  => 'WhatsApp',
        'download'  => 'Descarga directa',
    ];

    public function captacion()
    {
        return $this->belongsTo(Captacion::class);
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by_user_id');
    }

    public function getChannelLabelAttribute(): string
    {
        return self::CHANNELS[$this->channel] ?? ucfirst($this->channel);
    }

    public function wasOpened(): bool
    {
        return !is_null($this->email_opened_at);
    }

    public function wasPdfViewed(): bool
    {
        return !is_null($this->pdf_viewed_at);
    }

    public function scopeByChannel($q, string $channel)
    {
        return $q->where('channel', $channel);
    }
}
