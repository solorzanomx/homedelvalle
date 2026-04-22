<?php

namespace App\Actions\Carousel;

use App\Models\CarouselPost;
use App\Models\CarouselSlide;
use App\Models\CarouselVersion;
use App\Services\CarouselAIService;
use App\Services\TopicDiscoveryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateCarouselFromAIAction
{
    public function __construct(
        private readonly CarouselAIService    $aiService,
        private readonly TopicDiscoveryService $discovery,
    ) {}

    /**
     * Generate carousel content from AI and persist slides + captions.
     *
     * @param  CarouselPost  $carousel
     * @param  bool  $useWebSearch  Whether to enrich context via Perplexity
     * @return CarouselPost  Updated carousel with slides
     * @throws \Throwable
     */
    public function execute(CarouselPost $carousel, bool $useWebSearch = false): CarouselPost
    {
        // Mark as generating
        $carousel->update(['status' => 'generating']);

        try {
            DB::transaction(function () use ($carousel, $useWebSearch) {

                // 1. Build context (property/blog data + optional web search)
                $context = $this->discovery->buildContext($carousel, $useWebSearch);

                // 2. Call AI
                $result = $this->aiService->generate($carousel, $context);

                // 3. Snapshot the prompt for traceability
                $promptSummary = "Tipo: {$carousel->type} | Slides: " . count($result['slides'])
                    . " | WebSearch: " . ($useWebSearch ? 'sí' : 'no');

                // 4. Delete existing slides (regeneration flow)
                $carousel->slides()->delete();

                // 5. Create slides
                foreach ($result['slides'] as $slideData) {
                    CarouselSlide::create([
                        'carousel_post_id' => $carousel->id,
                        'order'            => $slideData['order'],
                        'type'             => $slideData['type'],
                        'headline'         => $slideData['headline'],
                        'subheadline'      => $slideData['subheadline'] ?? null,
                        'body'             => $slideData['body']        ?? null,
                        'cta_text'         => $slideData['cta_text']    ?? null,
                        'render_status'    => 'pending',
                    ]);
                }

                // 6. Update carousel with captions, hashtags and prompt used
                $carousel->update([
                    'caption_short'  => $result['caption_short'] ?? null,
                    'caption_long'   => $result['caption_long']  ?? null,
                    'hashtags'       => $result['hashtags']       ?? [],
                    'ai_prompt_used' => $promptSummary,
                    'status'         => 'review',
                ]);

                // 7. Create a version snapshot
                $versionNumber = ($carousel->versions()->max('version_number') ?? 0) + 1;

                CarouselVersion::create([
                    'carousel_post_id' => $carousel->id,
                    'version_number'   => $versionNumber,
                    'label'            => "Generación IA #{$versionNumber}",
                    'snapshot'         => [
                        'slides'        => $result['slides'],
                        'caption_short' => $result['caption_short'],
                        'caption_long'  => $result['caption_long'],
                        'hashtags'      => $result['hashtags'],
                    ],
                    'created_by'       => auth()->id(),
                ]);

            });

        } catch (\Throwable $e) {
            // Revert status to draft on failure
            $carousel->update(['status' => 'draft']);
            Log::error('GenerateCarouselFromAIAction failed', [
                'carousel_id' => $carousel->id,
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        }

        return $carousel->fresh(['slides', 'versions']);
    }
}
