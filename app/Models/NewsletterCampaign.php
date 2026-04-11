<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterCampaign extends Model
{
    protected $fillable = ['subject', 'body', 'status', 'sent_to_count', 'failed_count', 'created_by', 'sent_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDraft($q) { return $q->where('status', 'draft'); }
    public function scopeSent($q) { return $q->where('status', 'sent'); }
}
