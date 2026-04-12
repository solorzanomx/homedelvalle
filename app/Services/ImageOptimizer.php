<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    protected array $sizes = [
        'lg' => ['width' => 1200, 'quality' => 82],
        'md' => ['width' => 700, 'quality' => 85],
    ];

    /**
     * Process an uploaded image for blog posts (featured images).
     * Generates WebP at 1200px and 700px + keeps original as JPG fallback.
     * Uses pure GD — no external packages needed.
     */
    public function process(UploadedFile $file, string $directory, string $seoName = ''): array
    {
        $slug = $this->slugify($seoName ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $uid = substr(uniqid(), -6);
        $disk = Storage::disk('public');
        $sourcePath = $file->getRealPath();

        $source = $this->loadImage($sourcePath);
        if (!$source) {
            // Fallback: store original without processing
            $path = $file->store($directory, 'public');
            return ['original' => $path];
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        // Save JPG fallback at max 1200px
        $jpgImage = $this->resizeImage($source, $origW, $origH, 1200);
        $origPath = "{$directory}/{$slug}-{$uid}.jpg";
        $tmpJpg = tempnam(sys_get_temp_dir(), 'img');
        imagejpeg($jpgImage, $tmpJpg, 82);
        $disk->put($origPath, file_get_contents($tmpJpg));
        unlink($tmpJpg);
        imagedestroy($jpgImage);

        $variants = ['original' => $origPath];

        // Generate WebP variants
        if (function_exists('imagewebp')) {
            foreach ($this->sizes as $key => $config) {
                $resized = $this->resizeImage($source, $origW, $origH, $config['width']);
                $webpPath = "{$directory}/{$slug}-{$uid}-{$key}.webp";
                $tmpWebp = tempnam(sys_get_temp_dir(), 'webp');
                imagewebp($resized, $tmpWebp, $config['quality']);
                $disk->put($webpPath, file_get_contents($tmpWebp));
                unlink($tmpWebp);
                $variants[$key] = $webpPath;
                $variants["{$key}_width"] = imagesx($resized);
                imagedestroy($resized);
            }
        }

        imagedestroy($source);

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
        $sourcePath = $file->getRealPath();

        $source = $this->loadImage($sourcePath);
        if (!$source) {
            $path = $file->store($directory, 'public');
            return ['original' => $path, 'url' => $disk->url($path)];
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        // JPG fallback
        $jpgImage = $this->resizeImage($source, $origW, $origH, 1200);
        $jpgPath = "{$directory}/{$slug}-{$uid}.jpg";
        $tmpJpg = tempnam(sys_get_temp_dir(), 'img');
        imagejpeg($jpgImage, $tmpJpg, 82);
        $disk->put($jpgPath, file_get_contents($tmpJpg));
        unlink($tmpJpg);
        imagedestroy($jpgImage);

        $result = [
            'original' => $jpgPath,
            'url' => $disk->url($jpgPath),
        ];

        // WebP version
        if (function_exists('imagewebp')) {
            $webpImage = $this->resizeImage($source, $origW, $origH, 1200);
            $webpPath = "{$directory}/{$slug}-{$uid}.webp";
            $tmpWebp = tempnam(sys_get_temp_dir(), 'webp');
            imagewebp($webpImage, $tmpWebp, 82);
            $disk->put($webpPath, file_get_contents($tmpWebp));
            unlink($tmpWebp);
            imagedestroy($webpImage);
            $result['webp'] = $webpPath;
            $result['webp_url'] = $disk->url($webpPath);
        }

        imagedestroy($source);

        return $result;
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
     * Load image from file path using GD.
     */
    protected function loadImage(string $path): ?\GdImage
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        return match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => $this->loadPng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        } ?: null;
    }

    /**
     * Load PNG preserving transparency on a white background.
     */
    protected function loadPng(string $path): ?\GdImage
    {
        $img = @imagecreatefrompng($path);
        if (!$img) return null;

        $w = imagesx($img);
        $h = imagesy($img);
        $flat = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefill($flat, 0, 0, $white);
        imagecopy($flat, $img, 0, 0, 0, 0, $w, $h);
        imagedestroy($img);

        return $flat;
    }

    /**
     * Resize image maintaining aspect ratio. Returns new GD resource.
     */
    protected function resizeImage(\GdImage $source, int $origW, int $origH, int $maxWidth): \GdImage
    {
        if ($origW <= $maxWidth) {
            // No resize needed, return a copy
            $copy = imagecreatetruecolor($origW, $origH);
            imagecopy($copy, $source, 0, 0, 0, 0, $origW, $origH);
            return $copy;
        }

        $newW = $maxWidth;
        $newH = (int) round($origH * ($maxWidth / $origW));

        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Light sharpen after resize
        $sharpen = [
            [-1, -1, -1],
            [-1, 20, -1],
            [-1, -1, -1],
        ];
        imageconvolution($resized, $sharpen, 12, 0);

        return $resized;
    }

    protected function slugify(string $text): string
    {
        $slug = Str::slug($text);
        return $slug ?: 'imagen';
    }
}
