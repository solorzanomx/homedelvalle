<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    protected $fillable = ['email', 'source', 'ip_address', 'unsubscribe_token', 'client_id', 'subscribed_at', 'unsubscribed_at'];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $subscriber) {
            if (! $subscriber->unsubscribe_token) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
            if (! $subscriber->client_id) {
                $client = Client::where('email', $subscriber->email)->first();
                if ($client) {
                    $subscriber->client_id = $client->id;
                }
            }
        });
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeActive($q) { return $q->whereNull('unsubscribed_at'); }
    public function scopeUnsubscribed($q) { return $q->whereNotNull('unsubscribed_at'); }
}
