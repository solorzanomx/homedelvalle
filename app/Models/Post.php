<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'slug', 'excerpt', 'body', 'featured_image', 'category_id', 'status', 'published_at', 'meta_title', 'meta_description', 'views_count'];
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function category() { return $this->belongsTo(PostCategory::class, 'category_id'); }
    public function tags() { return $this->belongsToMany(Tag::class); }

    public function scopePublished($q) { return $q->where('status', 'published')->where('published_at', '<=', now()); }
    public function scopeDraft($q) { return $q->where('status', 'draft'); }
    public function scopeScheduled($q) { return $q->where('status', 'scheduled')->where('published_at', '>', now()); }
    public function scopeReadyToPublish($q) { return $q->where('status', 'scheduled')->where('published_at', '<=', now()); }
}
