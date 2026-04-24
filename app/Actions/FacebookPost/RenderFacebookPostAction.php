<?php

namespace App\Actions\FacebookPost;

use App\Models\FacebookPost;
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

        return view($viewName, compact('post'))->render();
    }
}
