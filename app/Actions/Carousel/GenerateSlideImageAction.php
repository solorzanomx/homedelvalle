<?php

namespace App\Actions\Carousel;

use App\Models\AiAgentConfig;
use App\Models\CarouselImagePrompt;
use App\Models\CarouselSlide;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateSlideImageAction
{
    private const DALLE_SIZE  = '1024x1792'; // portrait, cropped to 4:5 via object-fit:cover

    private function model(): string
    {
        return AiAgentConfig::optionsFor('carousel.image_generation')['model'] ?? 'dall-e-3';
    }

    /**
     * Per-template framing instructions sent to DALL-E.
     * Each value describes WHERE the text lives so the AI leaves that zone clear.
     */
    private const TEMPLATE_COMPOSITION = [
        // Dark overlay, center-left text block
        'premium-dark'    => 'FRAMING: text overlays the left-center area. Place the main subject on the RIGHT 45% of frame. Keep the left side darker with open negative space.',
        // White/light overlay covers most of slide, image is subtle background
        'hdv-claro'       => 'FRAMING: image appears behind a bright white overlay. Use soft, airy tones. Subject centered or right-aligned, with minimal contrast on the left.',
        // Blue gradient covers image; subject is a textured backdrop
        'hdv-degradado'   => 'FRAMING: image will be tinted by a blue gradient overlay. Place subject centrally or on the right. Cool, desaturated tones work best.',
        // Deep navy; left 8px stripe + center-left text
        'hdv-marino'      => 'FRAMING: text occupies the left-center. Place main visual subject on the RIGHT 45% of frame. Moody, dark lighting preferred.',
        // Heavy white overlay, editorial serif typography
        'hdv-editorial'   => 'FRAMING: image is a subtle background behind a near-opaque white overlay. Architectural or abstract subject; neutral, light tones.',
        // Minimal overlay; text panel anchors BOTTOM ~38% in a frosted dark band
        'hdv-foto-limpia' => 'FRAMING: CRITICAL — a dark frosted text panel occupies the BOTTOM 38% of the frame. The main subject MUST be in the upper 55-60%. Bottom third should be naturally darker or empty (open sky, receding ground, soft shadow) so it blends with the text panel.',
    ];

    /** @throws \Throwable */
    public function execute(CarouselSlide $slide): void
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('OPENAI_API_KEY no configurada en .env');
        }

        $carousel = $slide->carouselPost()->with('template')->first();
        $prompt   = $this->buildPrompt($slide, $carousel->template?->blade_view);

        Log::info('GenerateSlideImage: calling DALL-E', [
            'slide_id' => $slide->id,
            'type'     => $slide->type,
            'model'    => $this->model(),
            'size'     => self::DALLE_SIZE,
            'prompt'   => $prompt,
        ]);

        $response = Http::withToken($apiKey)
            ->timeout(90)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'           => $this->model(),
                'prompt'          => $prompt,
                'n'               => 1,
                'size'            => self::DALLE_SIZE,
                'quality'         => 'standard',
                'response_format' => 'url',
            ]);

        Log::info('GenerateSlideImage: DALL-E response', [
            'slide_id' => $slide->id,
            'status'   => $response->status(),
            'body'     => $response->body(),
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("DALL-E error ({$response->status()}): {$error}");
        }

        $imageUrl = $response->json('data.0.url');
        if (!$imageUrl) {
            throw new \RuntimeException('DALL-E did not return an image URL. Body: ' . $response->body());
        }

        $imageData = Http::timeout(60)->get($imageUrl)->body();

        $dir  = "carousels/{$slide->carousel_post_id}/backgrounds";
        $file = "{$dir}/{$slide->order}.png";

        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($file, $imageData);

        $slide->update(['background_image_path' => $file]);

        Log::info('GenerateSlideImage: done', ['slide_id' => $slide->id, 'file' => $file]);
    }

    public function buildPrompt(CarouselSlide $slide, ?string $templateBladeView = null): string
    {
        $base   = CarouselImagePrompt::forType($slide->type);
        $global = CarouselImagePrompt::globalSuffix();

        if (!$base && !$global) {
            CarouselImagePrompt::loadAll();
            $base   = CarouselImagePrompt::forType($slide->type);
            $global = CarouselImagePrompt::globalSuffix();
        }

        $context     = $slide->headline ? "Subject: {$slide->headline}. " : '';
        $composition = $templateBladeView
            ? (self::TEMPLATE_COMPOSITION[$templateBladeView] ?? '')
            : '';

        $parts = array_filter([
            $context . $base,
            $global,
            $composition,
        ]);

        return trim(implode('. ', $parts));
    }
}
