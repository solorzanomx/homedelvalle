<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = ['user_id', 'title', 'slug', 'excerpt', 'body', 'featured_image', 'featured_image_data', 'category_id', 'status', 'published_at', 'meta_title', 'meta_description', 'views_count', 'ctas', 'focus_keyword', 'secondary_keywords', 'seo_score', 'reading_time', 'schema_type', 'faq_schema', 'image_prompts', 'internal_links', 'ai_generated', 'ai_generation_status'];

    protected function casts(): array
    {
        return [
            'published_at'       => 'datetime',
            'views_count'        => 'integer',
            'ctas'               => 'array',
            'featured_image_data'=> 'array',
            'secondary_keywords' => 'array',
            'image_prompts'      => 'array',
            'internal_links'     => 'array',
            'faq_schema'         => 'array',
            'seo_score'          => 'integer',
            'reading_time'       => 'integer',
            'ai_generated'       => 'boolean',
        ];
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) return null;
        return Storage::disk('public')->url($this->featured_image);
    }

    public function getFeaturedImageWebpLgAttribute(): ?string
    {
        $data = $this->featured_image_data;
        return !empty($data['lg']) ? Storage::disk('public')->url($data['lg']) : null;
    }

    public function getFeaturedImageWebpMdAttribute(): ?string
    {
        $data = $this->featured_image_data;
        return !empty($data['md']) ? Storage::disk('public')->url($data['md']) : null;
    }

    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function category() { return $this->belongsTo(PostCategory::class, 'category_id'); }
    public function tags() { return $this->belongsToMany(Tag::class); }

    public function scopePublished($q) { return $q->where('status', 'published')->where('published_at', '<=', now()); }
    public function scopeDraft($q) { return $q->where('status', 'draft'); }
    public function scopeScheduled($q) { return $q->where('status', 'scheduled')->where('published_at', '>', now()); }
    public function scopeReadyToPublish($q) { return $q->where('status', 'scheduled')->where('published_at', '<=', now()); }

    public function getCta(int $index): ?array
    {
        $cta = $this->ctas[$index - 1] ?? null;
        if (!$cta || empty($cta['title'])) {
            return null;
        }
        return $cta;
    }

    public function getRenderedBodyAttribute(): string
    {
        $body = $this->body ?? '';

        return preg_replace_callback('/\{\{CTA(\d)\}\}/', function ($matches) {
            $cta = $this->getCta((int) $matches[1]);
            if (!$cta) {
                return '';
            }
            return view('blog._cta', ['cta' => $cta])->render();
        }, $body);
    }
}
