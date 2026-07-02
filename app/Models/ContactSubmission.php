<?php

namespace App\Models;

use App\Models\Concerns\HasAttribution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactSubmission extends Model
{
    use HasAttribution;

    protected $fillable = ['name', 'email', 'phone', 'message', 'property_id', 'ip_address', 'user_agent', 'utm_source', 'utm_medium', 'utm_campaign', 'landing_post_id', 'landing_label', 'is_read', 'ai_is_spam', 'ai_category', 'ai_urgency', 'ai_summary', 'ai_spam_reason'];
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

    public function landingPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'landing_post_id');
    }
}
