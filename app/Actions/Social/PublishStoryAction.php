<?php

namespace App\Actions\Social;

use App\Models\SiteSetting;
use App\Models\SocialStory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublishStoryAction
{
    public function execute(SocialStory $story): array
    {
        $settings = SiteSetting::first();

        $payload = [
            'type'             => 'story',
            'platform'         => $story->platform,
            'media_type'       => $story->media_type,
            'image_url'        => $story->rendered_image_path
                ? Storage::disk('public')->url($story->rendered_image_path)
                : null,
            'headline'         => $story->headline,
            'sticker_hashtags' => $story->sticker_hashtags,
            'sticker_location' => $story->sticker_location,
            'sticker_link'     => $story->sticker_link,
            'scheduled_at'     => $story->scheduled_at?->toIso8601String(),
        ];

        $webhookSent = false;

        if ($settings?->webhook_enabled && $settings?->webhook_api_key) {
            // Use general n8n webhook or a dedicated stories webhook
            $webhookUrl = config('services.n8n.stories_webhook_url')
                ?? config('services.n8n.carousel_webhook_url');

            if ($webhookUrl) {
                try {
                    Http::timeout(15)
                        ->withHeaders(['X-Api-Key' => $settings->webhook_api_key])
                        ->post($webhookUrl, $payload);

                    $webhookSent = true;
                    Log::info('PublishStoryAction: webhook enviado', [
                        'story_id'   => $story->id,
                        'platform'   => $story->platform,
                        'webhook_url' => $webhookUrl,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('PublishStoryAction: webhook falló', [
                        'story_id' => $story->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        } else {
            Log::info('PublishStoryAction: webhook no configurado, marcando como publicada', [
                'story_id' => $story->id,
            ]);
        }

        $story->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return [
            'success'      => true,
            'webhook_sent' => $webhookSent,
            'payload'      => $payload,
        ];
    }
}
