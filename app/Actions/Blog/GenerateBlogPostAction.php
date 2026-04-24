<?php

namespace App\Actions\Blog;

use App\Models\Post;
use App\Services\BlogAIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateBlogPostAction
{
    public function __construct(private readonly BlogAIService $blogAI) {}

    /**
     * Generate blog post content (text + SEO fields + image prompts).
     * Does NOT generate images — caller decides whether to do that.
     */
    public function execute(Post $post, string $title, array $keywords, string $marketData = '', array $brief = []): Post
    {
        set_time_limit(300);
        $post->update(['ai_generation_status' => 'generating']);

        try {
            $generated = $this->blogAI->generate($title, $keywords, $marketData, $brief);

            $slug = $generated['slug'] ?: Str::slug($generated['title']);
            $slug = $this->uniqueSlug($slug, $post->id);

            $post->update([
                'title'                => $generated['title'],
                'slug'                 => $slug,
                'excerpt'              => $generated['excerpt'],
                'body'                 => $generated['body'],
                'meta_title'           => $generated['meta_title'],
                'meta_description'     => $generated['meta_description'],
                'focus_keyword'        => $generated['focus_keyword'],
                'secondary_keywords'   => $generated['secondary_keywords'],
                'seo_score'            => $generated['seo_score'],
                'reading_time'         => $generated['reading_time'],
                'schema_type'          => $generated['schema_type'],
                'image_prompts'        => $generated['image_prompts'],
                'internal_links'       => $generated['internal_links'],
                'ctas'                 => $generated['ctas'],
                'ai_generated'         => true,
                'ai_generation_status' => 'done',
                'status'               => 'draft',
                'user_id'              => $post->user_id ?? Auth::id() ?? 1,
            ]);

            return $post->fresh();

        } catch (\Throwable $e) {
            Log::error('GenerateBlogPostAction failed', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);
            $post->update(['ai_generation_status' => 'failed']);
            throw $e;
        }
    }

    private function uniqueSlug(string $slug, int $excludeId): string
    {
        $original = $slug;
        $counter  = 1;

        while (Post::where('slug', $slug)->where('id', '!=', $excludeId)->exists()) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
