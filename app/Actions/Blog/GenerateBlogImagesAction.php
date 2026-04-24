<?php

namespace App\Actions\Blog;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class GenerateBlogImagesAction
{
    private const DALLE_SIZE   = '1792x1024'; // landscape 16:9 — source
    private const OUTPUT_WIDTH  = 720;         // px — stored/inserted width
    private const DALLE_MODEL  = 'dall-e-3';

    /**
     * Generate all 4 blog images (featured + 3 interior), store them,
     * set post->featured_image, and replace {{IMG1}} {{IMG2}} {{IMG3}} in body.
     */
    public function execute(Post $post): void
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            Log::warning('GenerateBlogImagesAction: OPENAI_API_KEY not set, skipping image generation');
            return;
        }

        $prompts = $post->image_prompts ?? [];

        if (empty($prompts)) {
            Log::warning('GenerateBlogImagesAction: no image_prompts on post', ['post_id' => $post->id]);
            return;
        }

        $dir  = "blog/{$post->id}";
        Storage::disk('public')->makeDirectory($dir);

        $body = $post->body;

        // Generate featured image
        if (!empty($prompts['featured'])) {
            try {
                $path = $this->generate($post->id, $prompts['featured'], "{$dir}/featured.png");
                $post->update(['featured_image' => $path]);
                Log::info('GenerateBlogImagesAction: featured image stored', ['path' => $path]);
            } catch (\Throwable $e) {
                Log::error('GenerateBlogImagesAction: featured image failed', [
                    'post_id' => $post->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        // Generate interior images 1-3 and replace placeholders in body
        foreach ([1 => 'interior_1', 2 => 'interior_2', 3 => 'interior_3'] as $num => $key) {
            if (empty($prompts[$key])) {
                continue;
            }

            try {
                $path    = $this->generate($post->id, $prompts[$key], "{$dir}/interior_{$num}.png");
                $imgUrl  = Storage::disk('public')->url($path);
                $imgHtml = "<figure class=\"blog-img\"><img src=\"{$imgUrl}\" alt=\"\" width=\"720\" loading=\"lazy\" style=\"width:720px;max-width:100%;height:auto;\"></figure>";
                $body    = str_replace("{{IMG{$num}}}", $imgHtml, $body);

                Log::info("GenerateBlogImagesAction: interior_{$num} stored", ['path' => $path]);
            } catch (\Throwable $e) {
                // Remove placeholder so HTML stays clean even on failure
                $body = str_replace("{{IMG{$num}}}", '', $body);

                Log::error("GenerateBlogImagesAction: interior_{$num} failed", [
                    'post_id' => $post->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        // Persist updated body (remove any remaining unfilled placeholders)
        $body = preg_replace('/\{\{IMG\d+\}\}/', '', $body);
        $post->update(['body' => $body]);
    }

    /**
     * Call DALL-E, download the image, store it, and return the storage path.
     */
    private function generate(int $postId, string $prompt, string $storagePath): string
    {
        Log::info('GenerateBlogImagesAction: calling DALL-E', [
            'post_id' => $postId,
            'path'    => $storagePath,
            'prompt'  => substr($prompt, 0, 150),
        ]);

        $response = Http::withToken(config('services.openai.api_key'))
            ->timeout(90)
            ->post('https://api.openai.com/v1/images/generations', [
                'model'           => self::DALLE_MODEL,
                'prompt'          => $prompt,
                'n'               => 1,
                'size'            => self::DALLE_SIZE,
                'quality'         => 'standard',
                'response_format' => 'url',
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("DALL-E error ({$response->status()}): {$error}");
        }

        $imageUrl = $response->json('data.0.url');
        if (!$imageUrl) {
            throw new \RuntimeException('DALL-E did not return an image URL');
        }

        $imageData = Http::timeout(60)->get($imageUrl)->body();

        // Resize to OUTPUT_WIDTH px wide, preserve aspect ratio
        $resized = Image::read($imageData)
            ->scaleDown(width: self::OUTPUT_WIDTH)
            ->toPng();

        Storage::disk('public')->put($storagePath, (string) $resized);

        return $storagePath;
    }
}
