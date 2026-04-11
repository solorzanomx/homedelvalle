<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name', 'role', 'content', 'video_url', 'avatar',
        'rating', 'is_featured', 'type', 'location', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        // Extract YouTube ID from various URL formats
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $this->video_url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }

        return $this->video_url;
    }
}
