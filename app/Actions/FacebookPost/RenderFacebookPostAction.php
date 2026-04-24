<?php

namespace App\Actions\FacebookPost;

use App\Models\FacebookPost;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class RenderFacebookPostAction
{
    public function execute(FacebookPost $post): void
    {
        $post->update(['render_status' => 'rendering', 'render_error' => null]);

        try {
            $html = $this->renderHtml($post);

            $dir  = "facebook/{$post->id}";
            $file = "{$dir}/image.png";

            Storage::disk('public')->makeDirectory($dir);
            $absolutePath = Storage::disk('public')->path($file);

            Browsershot::html($html)
                ->windowSize(1200, 628)
                ->setChromePath(config('browsershot.chrome_path'))
                ->setNodeBinary(config('browsershot.node_path'))
                ->setNpmBinary(config('browsershot.npm_path'))
                ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox', 'disable-dev-shm-usage'])
                ->waitUntilNetworkIdle()
                ->save($absolutePath);

            $post->update([
                'rendered_image_path' => $file,
                'render_status'       => 'done',
                'render_error'        => null,
            ]);

            Log::info('RenderFacebookPostAction: rendered', ['post_id' => $post->id, 'file' => $file]);
        } catch (\Throwable $e) {
            $post->update([
                'render_status' => 'failed',
                'render_error'  => substr($e->getMessage(), 0, 500),
            ]);
            throw $e;
        }
    }

    private function renderHtml(FacebookPost $post): string
    {
        $viewName = "facebook.templates.{$post->template}";

        if (!view()->exists($viewName)) {
            $viewName = 'facebook.templates.fb-dark';
        }

        $logoSrc = $this->logoBase64();

        return view($viewName, compact('post', 'logoSrc'))->render();
    }

    private function logoBase64(): string
    {
        $setting  = SiteSetting::first();
        $logoPath = $setting?->logo_path;

        if (!$logoPath) {
            return '';
        }

        $absolutePath = Storage::disk('public')->path($logoPath);

        if (!file_exists($absolutePath)) {
            return '';
        }

        $mime = mime_content_type($absolutePath) ?: 'image/png';
        $data = base64_encode(file_get_contents($absolutePath));

        return "data:{$mime};base64,{$data}";
    }
}
