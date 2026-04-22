<?php

namespace App\Jobs;

use App\Actions\Carousel\RenderSlideAction;
use App\Models\CarouselSlide;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenderCarouselSlideJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public readonly CarouselSlide $slide) {}

    public function handle(RenderSlideAction $action): void
    {
        $action->execute($this->slide);
    }

    public function failed(\Throwable $e): void
    {
        $this->slide->update([
            'render_status' => 'failed',
            'render_error'  => substr($e->getMessage(), 0, 500),
        ]);
    }
}
