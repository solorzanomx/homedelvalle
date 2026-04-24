<?php

namespace App\Actions\Blog;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GenerateBlogImagesAction
{
    private const DALLE_SIZE    = '1536x1024'; // landscape — gpt-image-1
    private const OUTPUT_WIDTH  = 720;
    private const DALLE_MODEL   = 'gpt-image-1';
    private const PROMPT_SUFFIX = 'Hyperrealistic, photorealistic, 8K ultra-HD, cinematic lighting, shot on Sony A7R V, professional commercial photography, sharp focus, no text, no watermarks, no logos, no people unless specified.';

    public const KEYS = ['featured', 'interior_1', 'interior_2', 'interior_3'];

    public const LABELS = [
        'featured'   => 'Imagen destacada',
        'interior_1' => 'Interior 1',
        'interior_2' => 'Interior 2',
        'interior_3' => 'Interior 3',
    ];

    // ──────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────

    /**
     * Full pipeline: generateAll + injectIntoBody.
     * Used by the async job to do everything in one shot.
     */
    public function execute(Post $post): void
    {
        $this->generateAll($post);
        $this->injectIntoBody($post->fresh());
    }

    /**
     * Generate all 4 images, store paths in image_prompts, set featured_image.
     * Does NOT modify body — call injectIntoBody() separately when ready.
     */
    public function generateAll(Post $post): void
    {
        $this->ensureApiKey();

        $prompts = $post->image_prompts ?? [];
        $dir     = "blog/{$post->id}";
        Storage::disk('public')->makeDirectory($dir);

        foreach (self::KEYS as $key) {
            if (empty($prompts[$key])) {
                continue;
            }

            try {
                $path = $this->callDalle($post->id, $prompts[$key], "{$dir}/{$key}.png");
                $this->storePath($post, $key, $path);

                if ($key === 'featured') {
                    $post->update(['featured_image' => $path]);
                }

                Log::info("GenerateBlogImagesAction: {$key} stored", ['path' => $path]);
            } catch (\Throwable $e) {
                Log::error("GenerateBlogImagesAction: {$key} failed", [
                    'post_id' => $post->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Re/generate a single image slot. Returns the public URL.
     */
    public function generateSingle(Post $post, string $key): string
    {
        if (!in_array($key, self::KEYS, true)) {
            throw new \InvalidArgumentException("Invalid image key: {$key}");
        }

        $this->ensureApiKey();

        $prompts = $post->image_prompts ?? [];

        if (empty($prompts[$key])) {
            throw new \RuntimeException("No hay prompt para la imagen: {$key}");
        }

        $dir  = "blog/{$post->id}";
        Storage::disk('public')->makeDirectory($dir);

        $path = $this->callDalle($post->id, $prompts[$key], "{$dir}/{$key}.png");
        $this->storePath($post, $key, $path);

        if ($key === 'featured') {
            $post->update(['featured_image' => $path]);
        }

        Log::info("GenerateBlogImagesAction: single {$key} stored", ['path' => $path]);

        return Storage::disk('public')->url($path) . '?t=' . time();
    }

    /**
     * Replace {{IMG1}} {{IMG2}} {{IMG3}} in post body with actual <figure><img> tags.
     * Safe to call multiple times — only replaces if placeholder is still present.
     */
    public function injectIntoBody(Post $post): void
    {
        $prompts = $post->image_prompts ?? [];
        $body    = $post->body;

        foreach ([1 => 'interior_1', 2 => 'interior_2', 3 => 'interior_3'] as $num => $key) {
            $placeholder = "{{IMG{$num}}}";

            if (!str_contains($body, $placeholder)) {
                continue; // already injected or removed — don't touch
            }

            $storedPath = $prompts["path_{$key}"] ?? null;

            if (!$storedPath) {
                $body = str_replace($placeholder, '', $body);
                continue;
            }

            $imgUrl  = Storage::disk('public')->url($storedPath);
            $imgHtml = "<figure class=\"blog-img\"><img src=\"{$imgUrl}\" alt=\"\" width=\"720\" loading=\"lazy\" style=\"width:720px;max-width:100%;height:auto;\"></figure>";
            $body    = str_replace($placeholder, $imgHtml, $body);
        }

        // Clean up any remaining unfilled placeholders
        $body = preg_replace('/\{\{IMG\d+\}\}/', '', $body);

        $post->update(['body' => $body]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function callDalle(int $postId, string $prompt, string $storagePath): string
    {
        $prompt = rtrim($prompt, '. ') . '. ' . self::PROMPT_SUFFIX;

        Log::info('GenerateBlogImagesAction: calling ' . self::DALLE_MODEL, [
            'post_id' => $postId,
            'path'    => $storagePath,
            'prompt'  => substr($prompt, 0, 150),
        ]);

        $response = Http::withToken(config('services.openai.api_key'))
            ->timeout(120)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'           => self::DALLE_MODEL,
                'prompt'          => $prompt,
                'n'               => 1,
                'size'            => self::DALLE_SIZE,
                'quality'         => 'high',
                'response_format' => 'b64_json',
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException(self::DALLE_MODEL . " error ({$response->status()}): {$error}");
        }

        $b64 = $response->json('data.0.b64_json');
        if (!$b64) {
            throw new \RuntimeException(self::DALLE_MODEL . ' did not return image data');
        }

        $imageData = base64_decode($b64);

        $manager = new ImageManager(new Driver());
        $resized  = $manager->read($imageData)
            ->scaleDown(width: self::OUTPUT_WIDTH)
            ->toPng();

        Storage::disk('public')->put($storagePath, (string) $resized);

        return $storagePath;
    }

    private function storePath(Post $post, string $key, string $path): void
    {
        $prompts = $post->image_prompts ?? [];
        $prompts["path_{$key}"] = $path;
        $post->update(['image_prompts' => $prompts]);
    }

    private function ensureApiKey(): void
    {
        if (!config('services.openai.api_key')) {
            throw new \RuntimeException('OPENAI_API_KEY no configurada en .env');
        }
    }
}
