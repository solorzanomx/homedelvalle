<?php

namespace App\Jobs;

use App\Actions\Carousel\GenerateCarouselFromAIAction;
use App\Models\CarouselPost;
use App\Models\CarouselTopicSuggestion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCarouselFromSuggestionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function __construct(
        public readonly CarouselPost             $carousel,
        public readonly CarouselTopicSuggestion  $suggestion,
    ) {}

    public function handle(GenerateCarouselFromAIAction $action): void
    {
        $action->execute($this->carousel, useWebSearch: false);

        $this->suggestion->update([
            'status'                => 'converted',
            'converted_carousel_id' => $this->carousel->id,
        ]);

        RenderCarouselAllJob::dispatch($this->carousel->fresh());
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateCarouselFromSuggestionJob failed', [
            'carousel_id'   => $this->carousel->id,
            'suggestion_id' => $this->suggestion->id,
            'error'         => $e->getMessage(),
        ]);

        $this->carousel->update(['status' => 'draft']);
    }
}
