<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactSubmission extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'message', 'property_id', 'ip_address', 'user_agent', 'utm_source', 'utm_medium', 'utm_campaign', 'is_read', 'ai_is_spam', 'ai_category', 'ai_urgency', 'ai_summary', 'ai_spam_reason'];
    protected function casts(): array
    {
        return [
            'is_read'    => 'boolean',
            'ai_is_spam' => 'boolean',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
