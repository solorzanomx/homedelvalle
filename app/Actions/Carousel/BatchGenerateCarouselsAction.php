<?php

namespace App\Actions\Carousel;

use App\Jobs\GenerateCarouselFromSuggestionJob;
use App\Models\CarouselPost;
use App\Models\CarouselTopicSuggestion;
use Illuminate\Support\Collection;

class BatchGenerateCarouselsAction
{
    /**
     * Create CarouselPost records from selected suggestions and dispatch
     * one generation job per carousel. Returns immediately — generation
     * happens in the background via queue worker.
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
            $carousel = CarouselPost::create([
                'title'   => $suggestion->title,
                'type'    => $suggestion->suggested_type,
                'status'  => 'draft',
                'user_id' => $userId ?: null,
            ]);

            $suggestion->update([
                'status'               => 'converted',
                'converted_carousel_id'=> $carousel->id,
            ]);

            GenerateCarouselFromSuggestionJob::dispatch($carousel, $suggestion);

            $created->push($carousel);
        }

        return $created;
    }
}
