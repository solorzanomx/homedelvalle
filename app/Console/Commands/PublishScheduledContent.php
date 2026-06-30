<?php

namespace App\Console\Commands;

use App\Actions\FacebookPost\PublishToFacebookAction;
use App\Models\CarouselPost;
use App\Models\CarouselPublication;
use App\Models\FacebookPost;
use App\Models\SocialStory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishScheduledContent extends Command
{
    protected $signature   = 'social:publish-scheduled';
    protected $description = 'Publica automáticamente Facebook Posts, Carruseles e Historias programados';

    public function handle(): int
    {
        $this->publishFacebookPosts();
        $this->publishCarousels();
        $this->publishStories();

        return self::SUCCESS;
    }

    // ── Facebook Posts ────────────────────────────────────────────────────────

    private function publishFacebookPosts(): void
    {
        $posts = FacebookPost::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($posts->isEmpty()) {
            return;
        }

        $this->info("Facebook Posts a publicar: {$posts->count()}");

        foreach ($posts as $post) {
            try {
                // Try full API publish if configured and image is ready
                $settings = \App\Models\SiteSetting::first();

                if (
                    $settings?->fb_api_enabled
                    && $settings?->fb_page_id
                    && $settings?->fb_page_access_token
                    && $post->render_status === 'done'
                ) {
                    $action = new PublishToFacebookAction();
                    $result = $action->execute($post);

                    $post->update([
                        'status'          => 'published',
                        'published_at'    => now(),
                        'fb_page_post_id' => $result['fb_page_post_id'],
                        'fb_post_url'     => $result['fb_post_url'],
                    ]);

                    $this->info("  ✓ FB Post #{$post->id} publicado en Facebook (API). URL: {$result['fb_post_url']}");
                    Log::info('PublishScheduledContent: FacebookPost publicado vía API', ['post_id' => $post->id]);
                } else {
                    // No API or no image — just mark as published
                    $post->update([
                        'status'       => 'published',
                        'published_at' => now(),
                    ]);

                    $reason = !($settings?->fb_api_enabled) ? 'API no habilitada' : 'imagen no renderizada';
                    $this->info("  ✓ FB Post #{$post->id} marcado como publicado ({$reason}).");
                    Log::info('PublishScheduledContent: FacebookPost marcado como publicado (sin API)', [
                        'post_id' => $post->id,
                        'reason'  => $reason,
                    ]);
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ FB Post #{$post->id} falló: {$e->getMessage()}");
                Log::error('PublishScheduledContent: error publicando FacebookPost', [
                    'post_id' => $post->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    // ── Carousel Posts ────────────────────────────────────────────────────────

    private function publishCarousels(): void
    {
        $carousels = CarouselPost::where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($carousels->isEmpty()) {
            return;
        }

        $this->info("Carruseles a publicar: {$carousels->count()}");

        $webhookUrl = config('services.n8n.carousel_webhook_url');

        foreach ($carousels as $carousel) {
            try {
                // Create a CarouselPublication record
                $publication = CarouselPublication::create([
                    'carousel_post_id' => $carousel->id,
                    'channel'          => 'instagram',
                    'status'           => 'pending',
                    'scheduled_at'     => $carousel->scheduled_at,
                ]);

                // Fire n8n webhook
                if ($webhookUrl) {
                    $carousel->load(['slides' => fn($q) => $q->orderBy('order'), 'user', 'approvedBy']);

                    $payload = [
                        'carousel_id'   => $carousel->id,
                        'title'         => $carousel->title,
                        'type'          => $carousel->type,
                        'caption_short' => $carousel->caption_short,
                        'caption_long'  => $carousel->caption_long,
                        'hashtags'      => $carousel->hashtags_string,
                        'cta'           => $carousel->cta,
                        'approved_at'   => $carousel->approved_at?->toIso8601String(),
                        'approved_by'   => $carousel->approvedBy?->name,
                        'scheduled_at'  => $carousel->scheduled_at?->toIso8601String(),
                        'slides'        => $carousel->slides->map(fn($s) => [
                            'order'     => $s->order,
                            'type'      => $s->type,
                            'headline'  => $s->headline,
                            'image_url' => $s->rendered_image_path
                                ? \Storage::url($s->rendered_image_path)
                                : null,
                        ]),
                    ];

                    Http::timeout(15)->post($webhookUrl, $payload);

                    $publication->update(['status' => 'sent', 'sent_at' => now()]);
                    $carousel->update(['status' => 'published', 'published_at' => now()]);

                    $this->info("  ✓ Carrusel #{$carousel->id} enviado a n8n y marcado como publicado.");
                    Log::info('PublishScheduledContent: Carrusel enviado a n8n', ['carousel_id' => $carousel->id]);
                } else {
                    $publication->update(['status' => 'pending']);
                    $carousel->update(['status' => 'published', 'published_at' => now()]);

                    $this->info("  ✓ Carrusel #{$carousel->id} marcado como publicado (sin webhook n8n).");
                    Log::info('PublishScheduledContent: Carrusel publicado sin webhook', ['carousel_id' => $carousel->id]);
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ Carrusel #{$carousel->id} falló: {$e->getMessage()}");
                Log::error('PublishScheduledContent: error publicando Carrusel', [
                    'carousel_id' => $carousel->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    // ── Social Stories ────────────────────────────────────────────────────────

    private function publishStories(): void
    {
        $stories = SocialStory::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($stories->isEmpty()) {
            return;
        }

        $this->info("Historias a publicar: {$stories->count()}");

        foreach ($stories as $story) {
            try {
                $action = new \App\Actions\Social\PublishStoryAction();
                $action->execute($story);

                $this->info("  ✓ Historia #{$story->id} publicada.");
                Log::info('PublishScheduledContent: Historia publicada', ['story_id' => $story->id]);
            } catch (\Throwable $e) {
                $story->update(['status' => 'failed']);
                $this->error("  ✗ Historia #{$story->id} falló: {$e->getMessage()}");
                Log::error('PublishScheduledContent: error publicando Historia', [
                    'story_id' => $story->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }
}
