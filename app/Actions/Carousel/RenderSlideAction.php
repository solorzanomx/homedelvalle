<?php

namespace App\Actions\Carousel;

use App\Models\CarouselPost;
use App\Models\CarouselSlide;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class RenderSlideAction
{
    /** Slide types that have a dedicated Blade view in premium-dark template */
    private const KNOWN_TYPES = [
        'cover', 'key_stat', 'explanation', 'benefit',
        'problem', 'social_proof', 'cta', 'example',
    ];

    /** @throws \Throwable */
    public function execute(CarouselSlide $slide): void
    {
        $carousel = $slide->carouselPost()->with('slides')->first();

        $slide->update(['render_status' => 'rendering', 'render_error' => null]);

        try {
            $html = $this->renderHtml($slide, $carousel);

            $dir  = "carousels/{$carousel->id}/slides";
            $file = "{$dir}/{$slide->order}.png";

            Storage::disk('public')->makeDirectory($dir);
            $absolutePath = Storage::disk('public')->path($file);

            Browsershot::html($html)
                ->windowSize(1080, 1080)
                ->deviceScaleFactor(2)
                ->setNodeBinary(config('browsershot.node_binary', '/usr/bin/node'))
                ->setNpmBinary(config('browsershot.npm_binary', '/usr/bin/npm'))
                ->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'])
                ->waitUntilNetworkIdle()
                ->screenshot($absolutePath);

            $slide->update([
                'rendered_image_path' => $file,
                'render_status'       => 'done',
                'render_error'        => null,
            ]);
        } catch (\Throwable $e) {
            $slide->update([
                'render_status' => 'failed',
                'render_error'  => substr($e->getMessage(), 0, 500),
            ]);
            throw $e;
        }
    }

    private function renderHtml(CarouselSlide $slide, CarouselPost $carousel): string
    {
        $type         = in_array($slide->type, self::KNOWN_TYPES) ? $slide->type : 'generic';
        $viewName     = "carousels.templates.premium-dark.{$type}";
        $totalSlides  = $carousel->slides->count();

        return view($viewName, compact('slide', 'carousel', 'totalSlides'))->render();
    }
}
