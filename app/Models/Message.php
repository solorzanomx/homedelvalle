<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    protected $fillable = [
        'client_id', 'user_id', 'enrollment_id', 'channel', 'direction',
        'subject', 'body', 'status', 'external_id',
        'sent_at', 'delivered_at', 'opened_at', 'replied_at',
        'open_count', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'opened_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo     { return $this->belongsTo(Client::class); }
    public function user(): BelongsTo       { return $this->belongsTo(User::class); }
    public function enrollment(): BelongsTo { return $this->belongsTo(AutomationEnrollment::class); }

    // ── Scopes ────────────────────────────────────────
    public function scopeChannel($q, string $ch)  { return $q->where('channel', $ch); }
    public function scopeEmails($q)                { return $q->channel('email'); }
    public function scopeWhatsapp($q)              { return $q->channel('whatsapp'); }
    public function scopeSent($q)                  { return $q->where('status', '!=', 'queued'); }
    public function scopeOpened($q)                { return $q->whereNotNull('opened_at'); }

    // ── Helpers ───────────────────────────────────────
    public function markSent(): void    { $this->update(['status' => 'sent', 'sent_at' => now()]); }
    public function markOpened(): void  { $this->increment('open_count'); $this->update(['status' => 'opened', 'opened_at' => $this->opened_at ?? now()]); }
    public function markReplied(): void { $this->update(['status' => 'replied', 'replied_at' => now()]); }
    public function markFailed(): void  { $this->update(['status' => 'failed']); }
    public function markSkipped(): void { $this->update(['status' => 'skipped']); }
}
