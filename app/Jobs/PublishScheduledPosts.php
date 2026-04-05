<?php

namespace App\Jobs;

use App\Models\Post;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts
{
    public function handle(): void
    {
        $count = Post::readyToPublish()->update(['status' => 'published']);

        if ($count > 0) {
            Log::info("PublishScheduledPosts: published {$count} posts");
        }
    }
}
