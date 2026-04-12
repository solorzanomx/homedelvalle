<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    protected ImageManager $manager;

    protected array $sizes = [
        'lg' => ['width' => 1200, 'quality' => 82],
        'md' => ['width' => 700, 'quality' => 85],
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Process an uploaded image for blog posts (featured images).
     * Generates WebP at 1200px and 700px + keeps original as JPG fallback.
     * Returns JSON-serializable array with all paths.
     */
    public function process(UploadedFile $file, string $directory, string $seoName = ''): array
    {
        $slug = $this->slugify($seoName ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uid = substr(uniqid(), -6);
        $disk = Storage::disk('public');

        // Original resized to max 1200px as JPG fallback
        $original = $this->manager->read($file->getRealPath());
        if ($original->width() > 1200) {
            $original->scaleDown(width: 1200);
        }
        $origPath = "{$directory}/{$slug}-{$uid}.jpg";
        $disk->put($origPath, (string) $original->toJpeg(82));

        $variants = ['original' => $origPath];

        // Generate WebP variants
        foreach ($this->sizes as $key => $config) {
            $img = $this->manager->read($file->getRealPath());
            if ($img->width() > $config['width']) {
                $img->scaleDown(width: $config['width']);
            }
            $webpPath = "{$directory}/{$slug}-{$uid}-{$key}.webp";
            $disk->put($webpPath, (string) $img->toWebp($config['quality']));
            $variants[$key] = $webpPath;
            $variants["{$key}_width"] = min($img->width(), $config['width']);
        }

        return $variants;
    }

    /**
     * Process an inline CMS image (from TinyMCE editor).
     * Single 1200px WebP + JPG fallback.
     */
    public function processInline(UploadedFile $file, string $directory): array
    {
        $slug = $this->slugify(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uid = substr(uniqid(), -6);
        $disk = Storage::disk('public');

        $img = $this->manager->read($file->getRealPath());
        if ($img->width() > 1200) {
            $img->scaleDown(width: 1200);
        }

        $jpgPath = "{$directory}/{$slug}-{$uid}.jpg";
        $disk->put($jpgPath, (string) $img->toJpeg(82));

        $webpPath = "{$directory}/{$slug}-{$uid}.webp";
        $disk->put($webpPath, (string) $img->toWebp(82));

        return [
            'original' => $jpgPath,
            'webp' => $webpPath,
            'url' => $disk->url($jpgPath),
            'webp_url' => $disk->url($webpPath),
        ];
    }

    /**
     * Delete all variant files for a given image data array.
     */
    public function cleanup(array|string|null $imageData): void
    {
        if (!$imageData) return;

        $disk = Storage::disk('public');

        if (is_string($imageData)) {
            $disk->delete($imageData);
            return;
        }

        foreach (['original', 'lg', 'md'] as $key) {
            if (!empty($imageData[$key])) {
                $disk->delete($imageData[$key]);
            }
        }
    }

    /**
     * Generate SEO-friendly slug from text.
     */
    protected function slugify(string $text): string
    {
        $slug = Str::slug($text);
        return $slug ?: 'imagen';
    }
}
