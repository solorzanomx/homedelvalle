<?php

namespace App\Actions\Carousel;

use App\Models\CarouselImagePrompt;
use App\Models\CarouselSlide;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GenerateSlideImageAction
{
    private const IMAGE_MODEL  = 'gemini-3.1-flash-image-preview';
    private const OUTPUT_WIDTH = 1080; // portrait slides — height scales naturally

    /**
     * Per-template framing instructions.
     * Each value describes WHERE the text lives so the AI leaves that zone clear.
     */
    private const TEMPLATE_COMPOSITION = [
        'premium-dark'    => 'FRAMING: text overlays the left-center area. Place the main subject on the RIGHT 45% of frame. Keep the left side darker with open negative space.',
        'hdv-claro'       => 'FRAMING: image appears behind a bright white overlay. Use soft, airy tones. Subject centered or right-aligned, with minimal contrast on the left.',
        'hdv-degradado'   => 'FRAMING: image will be tinted by a blue gradient overlay. Place subject centrally or on the right. Cool, desaturated tones work best.',
        'hdv-marino'      => 'FRAMING: text occupies the left-center. Place main visual subject on the RIGHT 45% of frame. Moody, dark lighting preferred.',
        'hdv-editorial'   => 'FRAMING: image is a subtle background behind a near-opaque white overlay. Architectural or abstract subject; neutral, light tones.',
        'hdv-foto-limpia' => 'FRAMING: CRITICAL — a dark frosted text panel occupies the BOTTOM 38% of the frame. The main subject MUST be in the upper 55-60%. Bottom third should be naturally darker or empty so it blends with the text panel.',
    ];

    /** @throws \Throwable */
    public function execute(CarouselSlide $slide): void
    {
        $apiKey = config('services.google_ai.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('GOOGLE_AI_STUDIO_KEY no configurada en .env');
        }

        $carousel = $slide->carouselPost()->with('template')->first();
        $prompt   = $this->buildPrompt($slide, $carousel->template?->blade_view);

        Log::info('GenerateSlideImage: calling ' . self::IMAGE_MODEL, [
            'slide_id' => $slide->id,
            'type'     => $slide->type,
            'prompt'   => substr($prompt, 0, 200),
        ]);

        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout(120)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/' . self::IMAGE_MODEL . ':generateContent', [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'responseModalities' => ['image', 'text'],
                ],
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException(self::IMAGE_MODEL . " error ({$response->status()}): {$error}");
        }

        $b64 = null;
        foreach ($response->json('candidates.0.content.parts', []) as $part) {
            if (!empty($part['inlineData']['data'])) {
                $b64 = $part['inlineData']['data'];
                break;
            }
        }

        if (!$b64) {
            throw new \RuntimeException(self::IMAGE_MODEL . ' did not return image data. Body: ' . substr($response->body(), 0, 400));
        }

        $manager = new ImageManager(new Driver());
        $resized  = $manager->read(base64_decode($b64))
            ->scaleDown(width: self::OUTPUT_WIDTH)
            ->toPng();

        $dir  = "carousels/{$slide->carousel_post_id}/backgrounds";
        $file = "{$dir}/{$slide->order}.png";

        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($file, (string) $resized);

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
