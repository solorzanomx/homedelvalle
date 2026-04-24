<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogTopicSuggestion extends Model
{
    protected $fillable = [
        'session_id', 'title', 'description', 'reasoning',
        'suggested_keywords', 'relevance_score', 'status', 'converted_post_id',
    ];

    protected function casts(): array
    {
        return [
            'suggested_keywords' => 'array',
            'relevance_score'    => 'integer',
        ];
    }

    public function convertedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'converted_post_id');
    }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeForSession($q, string $sessionId) { return $q->where('session_id', $sessionId); }
}
