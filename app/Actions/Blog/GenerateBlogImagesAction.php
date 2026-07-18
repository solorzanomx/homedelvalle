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
    private const IMAGE_MODEL   = 'gemini-3.1-flash-image';
    private const OUTPUT_WIDTH  = 720;
    private const PROMPT_SUFFIX = 'Ultra photorealistic, shot on full-frame DSLR, natural lighting, sharp focus, high detail, 4K resolution, aspect ratio 16:9, no text, no logos, no watermarks, no overlays, no UI elements, no borders, no artificial filters — if any signage, street signs, real estate signs or commercial text appears in the scene, render it exclusively in Spanish, Mexico City context.';

    /**
     * Va AL INICIO del prompt (ahí pesa más): los rostros generados salen
     * difuminados o falsos y dan desconfianza — se compone sin caras.
     */
    private const PROMPT_PREFIX = 'HARD CONSTRAINT — NO HUMAN FACES ANYWHERE IN THE IMAGE: every person, if any, must be photographed strictly from behind or as hands/arms only in close-up; no profiles, no partial faces, no reflections of faces. Prefer composing the scene entirely without people. Scene: ';

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

        // Generate a single seed for all 4 images so they share the same palette/feel
        $seed = $prompts['seed'] ?? random_int(1, 2_147_483_647);

        // Persist seed immediately so single-image regenerations can reuse it
        $prompts['seed'] = $seed;
        $post->update(['image_prompts' => $prompts]);
        $post->refresh();
        $prompts = $post->image_prompts;

        foreach (self::KEYS as $key) {
            if (empty($prompts[$key])) {
                continue;
            }

            try {
                $path = $this->callGemini($post->id, $prompts[$key], $this->newPath($post->id, $key), $seed);
                $this->storePath($post, $key, $path);

                if ($key === 'featured') {
                    $post->update(['featured_image' => $path]);
                }

                Log::info("GenerateBlogImagesAction: {$key} stored", ['path' => $path, 'seed' => $seed]);
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

        // Seed NUEVA en cada regeneración: con la seed guardada el resultado es
        // determinístico y "Re-generar" devolvía exactamente la misma imagen.
        // (La seed compartida solo aplica a la tanda inicial de generateAll.)
        $seed = random_int(1, 2_147_483_647);

        $path = $this->callGemini($post->id, $prompts[$key], $this->newPath($post->id, $key), $seed);
        $this->storePath($post, $key, $path);

        if ($key === 'featured') {
            $post->update(['featured_image' => $path]);
        }

        Log::info("GenerateBlogImagesAction: single {$key} stored", ['path' => $path, 'seed' => $seed]);

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

    private function ensureApiKey(): void
    {
        if (!config('services.google_ai.api_key')) {
            throw new \RuntimeException('GOOGLE_AI_STUDIO_KEY no configurada en .env');
        }
    }

    private function callGemini(int $postId, string $prompt, string $storagePath, ?int $seed = null): string
    {
        $prompt = self::PROMPT_PREFIX . rtrim($prompt, '. ') . '. ' . self::PROMPT_SUFFIX;

        Log::info('GenerateBlogImagesAction: calling ' . self::IMAGE_MODEL, [
            'post_id' => $postId,
            'path'    => $storagePath,
            'prompt'  => substr($prompt, 0, 150),
            'seed'    => $seed,
        ]);

        $apiKey = config('services.google_ai.api_key');

        $generationConfig = [
            'responseModalities' => ['image', 'text'],
        ];

        if ($seed !== null) {
            $generationConfig['seed'] = $seed;
        }

        $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
            ->timeout(120)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/" . self::IMAGE_MODEL . ":generateContent", [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => $generationConfig,
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

        $imageData = base64_decode($b64);

        $manager = new ImageManager(new Driver());
        $resized  = $manager->read($imageData)
            ->scaleDown(width: self::OUTPUT_WIDTH)
            ->toPng();

        Storage::disk('public')->put($storagePath, (string) $resized);

        return $storagePath;
    }

    /**
     * Nombre versionado por generación: reutilizar el mismo nombre dejaba la
     * imagen vieja cacheada en navegador/Cloudflare y "no se veía" el cambio.
     */
    private function newPath(int $postId, string $key): string
    {
        return "blog/{$postId}/{$key}-" . \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)) . '.png';
    }

    private function storePath(Post $post, string $key, string $path): void
    {
        $post->refresh();
        $prompts = $post->image_prompts ?? [];
        $old     = $prompts["path_{$key}"] ?? null;

        $prompts["path_{$key}"] = $path;
        $post->update(['image_prompts' => $prompts]);

        if ($old && $old !== $path) {
            // Si la imagen vieja ya estaba insertada en el body, apuntar a la nueva
            $oldUrl = Storage::disk('public')->url($old);
            $newUrl = Storage::disk('public')->url($path);
            if (str_contains($post->body ?? '', $oldUrl)) {
                $post->update(['body' => str_replace($oldUrl, $newUrl, $post->body)]);
            }

            Storage::disk('public')->delete($old);
        }
    }
}
