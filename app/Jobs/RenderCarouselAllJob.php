<?php

namespace App\Jobs;

use App\Models\CarouselPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class RenderCarouselAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 600;

    public function __construct(public readonly CarouselPost $carousel) {}

    public function handle(): void
    {
        $slides = $this->carousel->slides()->orderBy('order')->get();

        $jobs = $slides->map(fn ($slide) => new RenderCarouselSlideJob($slide));

        Bus::chain($jobs)->dispatch();
    }
}
