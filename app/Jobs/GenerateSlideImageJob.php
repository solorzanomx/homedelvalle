<?php

namespace App\Jobs;

use App\Actions\Carousel\GenerateSlideImageAction;
use App\Models\CarouselSlide;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSlideImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 90;

    public function __construct(public readonly CarouselSlide $slide) {}

    public function handle(GenerateSlideImageAction $action): void
    {
        $action->execute($this->slide);
    }

    public function failed(\Throwable $e): void
    {
        Log::warning('GenerateSlideImageJob failed', [
            'slide_id' => $this->slide->id,
            'error'    => $e->getMessage(),
        ]);
    }
}
