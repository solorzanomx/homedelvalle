<?php

namespace App\Jobs;

use App\Actions\Blog\GenerateBlogPostAction;
use App\Models\BlogTopicSuggestion;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBlogPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300; // 5 min — Claude can be slow on long posts

    public function __construct(
        public readonly Post    $post,
        public readonly string  $title,
        public readonly array   $keywords,
        public readonly string  $marketData        = '',
        public readonly ?int    $suggestionId      = null,
    ) {}

    public function handle(
        GenerateBlogPostAction $action,
        \App\Actions\Blog\GenerateBlogImagesAction $imageAction,
    ): void {
        $post = $action->execute($this->post, $this->title, $this->keywords, $this->marketData);

        // For async jobs, do the full pipeline automatically
        try {
            $imageAction->execute($post);
        } catch (\Throwable $e) {
            Log::warning('GenerateBlogPostJob: image generation failed (non-fatal)', [
                'post_id' => $post->id,
                'error'   => $e->getMessage(),
            ]);
        }

        if ($this->suggestionId) {
            BlogTopicSuggestion::find($this->suggestionId)?->update([
                'status'             => 'converted',
                'converted_post_id'  => $this->post->id,
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateBlogPostJob failed', [
            'post_id' => $this->post->id,
            'error'   => $e->getMessage(),
        ]);

        $this->post->update(['ai_generation_status' => 'failed']);
    }
}
