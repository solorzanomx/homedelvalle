<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledBlog extends Command
{
    protected $signature   = 'blog:publish-scheduled';
    protected $description = 'Publica automáticamente posts de blog que tengan status=scheduled y published_at <= now()';

    public function handle(): int
    {
        $posts = Post::where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No hay posts de blog programados para publicar.');
            return self::SUCCESS;
        }

        $this->info("Posts de blog a publicar: {$posts->count()}");

        foreach ($posts as $post) {
            try {
                $post->update(['status' => 'published']);
                $this->info("  ✓ Post #{$post->id} \"{$post->title}\" publicado.");
                Log::info('PublishScheduledBlog: post publicado', ['post_id' => $post->id, 'title' => $post->title]);
            } catch (\Throwable $e) {
                $this->error("  ✗ Post #{$post->id} falló: {$e->getMessage()}");
                Log::error('PublishScheduledBlog: error publicando post', [
                    'post_id' => $post->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
