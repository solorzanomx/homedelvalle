<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageOptimizer
{
    protected ImageManager $manager;
    protected int $maxWidth = 1920;
    protected int $maxHeight = 1920;
    protected int $quality = 78;
    protected int $thumbWidth = 400;
    protected int $thumbHeight = 300;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Optimize an uploaded image: resize if too large, compress, optionally convert to WebP.
     * Returns the relative storage path.
     */
    public function optimize(UploadedFile $file, string $directory, bool $createWebp = true): array
    {
        $image = $this->manager->read($file->getRealPath());

        // Resize if larger than max dimensions (maintain aspect ratio)
        $width = $image->width();
        $height = $image->height();
        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $image->scaleDown($this->maxWidth, $this->maxHeight);
        }

        // Generate unique filename
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = \Str::slug($basename) ?: 'image';
        $filename = $basename . '-' . uniqid() . '.jpg';

        // Encode as JPEG with quality
        $encoded = $image->toJpeg($this->quality);
        $path = $directory . '/' . $filename;
        Storage::disk('public')->put($path, (string) $encoded);

        $result = ['path' => $path, 'webp_path' => null, 'thumb_path' => null];

        // Create WebP version
        if ($createWebp && function_exists('imagewebp')) {
            $webpFilename = $basename . '-' . uniqid() . '.webp';
            $webpPath = $directory . '/' . $webpFilename;
            $webpEncoded = $image->toWebp($this->quality);
            Storage::disk('public')->put($webpPath, (string) $webpEncoded);
            $result['webp_path'] = $webpPath;
        }

        // Create thumbnail
        $thumb = $this->manager->read($file->getRealPath());
        $thumb->cover($this->thumbWidth, $this->thumbHeight);
        $thumbFilename = $basename . '-thumb-' . uniqid() . '.jpg';
        $thumbPath = $directory . '/thumbs/' . $thumbFilename;
        Storage::disk('public')->put($thumbPath, (string) $thumb->toJpeg($this->quality));
        $result['thumb_path'] = $thumbPath;

        return $result;
    }

    /**
     * Optimize an already-stored image by path (for batch processing).
     */
    public function optimizeExisting(string $storagePath): bool
    {
        $fullPath = Storage::disk('public')->path($storagePath);
        if (!file_exists($fullPath)) {
            return false;
        }

        $image = $this->manager->read($fullPath);
        $width = $image->width();
        $height = $image->height();

        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $image->scaleDown($this->maxWidth, $this->maxHeight);
        }

        $encoded = $image->toJpeg($this->quality);
        Storage::disk('public')->put($storagePath, (string) $encoded);
        return true;
    }

    /**
     * Process avatar: resize to square, compress
     */
    public function processAvatar(UploadedFile $file, string $directory): string
    {
        $image = $this->manager->read($file->getRealPath());
        $image->cover(400, 400);

        $filename = 'avatar-' . uniqid() . '.jpg';
        $path = $directory . '/' . $filename;
        $encoded = $image->toJpeg($this->quality);
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }

    public function setMaxDimensions(int $width, int $height): self
    {
        $this->maxWidth = $width;
        $this->maxHeight = $height;
        return $this;
    }

    public function setQuality(int $quality): self
    {
        $this->quality = $quality;
        return $this;
    }
}
