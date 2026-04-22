<?php

namespace App\Actions\Carousel;

use App\Jobs\RenderCarouselAllJob;
use App\Models\CarouselPost;
use App\Models\CarouselTopicSuggestion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchGenerateCarouselsAction
{
    public function __construct(
        private readonly GenerateCarouselFromAIAction $generator
    ) {}

    /**
     * For each selected suggestion: create a CarouselPost, run AI generation,
     * dispatch rendering, and mark the suggestion as converted.
     *
     * @param  array $suggestionIds
     * @param  int   $userId
     * @return Collection<CarouselPost>
     */
    public function execute(array $suggestionIds, int $userId = 0): Collection
    {
        $suggestions = CarouselTopicSuggestion::whereIn('id', $suggestionIds)
            ->where('status', 'pending')
            ->get();

        $created = collect();

        foreach ($suggestions as $suggestion) {
            DB::beginTransaction();
            try {
                // Create the carousel post from the suggestion
                $carousel = CarouselPost::create([
                    'title'       => $suggestion->title,
                    'type'        => $suggestion->suggested_type,
                    'status'      => 'draft',
                    'cta'         => null,
                    'user_id'     => $userId ?: null,
                ]);

                // Generate content with AI (no web search for batch; avoids rate limits)
                $this->generator->execute($carousel, useWebSearch: false);

                // Mark suggestion as converted
                $suggestion->update([
                    'status'                => 'converted',
                    'converted_carousel_id' => $carousel->id,
                ]);

                DB::commit();

                // Dispatch rendering (async, outside transaction)
                RenderCarouselAllJob::dispatch($carousel->fresh());

                $created->push($carousel);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('BatchGenerateCarouselsAction failed for suggestion', [
                    'suggestion_id' => $suggestion->id,
                    'error'         => $e->getMessage(),
                ]);
                // Continue with remaining suggestions
            }
        }

        return $created;
    }
}
