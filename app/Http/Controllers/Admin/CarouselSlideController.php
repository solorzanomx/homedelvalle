<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\GenerateSlideImageAction;
use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use App\Models\CarouselSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarouselSlideController extends Controller
{
    /** Update slide text fields (autosave via AJAX) */
    public function update(Request $request, CarouselPost $carousel, CarouselSlide $slide): JsonResponse
    {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        $data = $request->validate([
            'headline'    => ['nullable', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string', 'max:255'],
            'body'        => ['nullable', 'string', 'max:1000'],
            'cta_text'    => ['nullable', 'string', 'max:120'],
        ]);

        // Mark rendered slide as stale when content changes
        $wasRendered = $slide->render_status === 'done';
        if ($wasRendered) {
            $data['render_status'] = 'pending';
        }

        $slide->update($data);

        return response()->json([
            'ok'    => true,
            'stale' => $wasRendered,
        ]);
    }

    /** Generate DALL-E images for all slides — synchronous, no queue needed */
    public function generateImages(Request $request, CarouselPost $carousel, GenerateSlideImageAction $action): JsonResponse|RedirectResponse
    {
        $slides = $carousel->slides()->orderBy('order')->get();

        if ($slides->isEmpty()) {
            if ($request->expectsJson()) return response()->json(['ok' => false, 'message' => 'No hay slides.'], 422);
            return back()->with('error', 'No hay slides para generar imágenes.');
        }

        $results = [];
        foreach ($slides as $slide) {
            try {
                $action->execute($slide);
                $bgUrl = $slide->fresh()->background_image_path
                    ? '/storage/' . $slide->fresh()->background_image_path
                    : null;
                $results[] = ['id' => $slide->id, 'ok' => true, 'bg_url' => $bgUrl];
            } catch (\Throwable $e) {
                $results[] = ['id' => $slide->id, 'ok' => false, 'message' => $e->getMessage()];
            }
        }

        if ($request->expectsJson()) return response()->json(['ok' => true, 'slides' => $results]);
        return back()->with('success', "Imágenes generadas para {$slides->count()} slides.");
    }

    /** Generate DALL-E image for a single slide — runs synchronously so no queue needed */
    public function generateImage(
        Request $request,
        CarouselPost $carousel,
        CarouselSlide $slide,
        GenerateSlideImageAction $action,
    ): JsonResponse|RedirectResponse {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        try {
            $action->execute($slide);
            $bgUrl = $slide->fresh()->background_image_path
                ? '/storage/' . $slide->fresh()->background_image_path
                : null;
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error al generar imagen: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'bg_url' => $bgUrl]);
        }

        return back()->with('success', "Imagen del slide #{$slide->order} generada.");
    }

    /** Upload a custom background image for a slide */
    public function uploadBackground(Request $request, CarouselPost $carousel, CarouselSlide $slide): JsonResponse|RedirectResponse
    {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        $request->validate([
            'background' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $dir  = "carousels/{$carousel->id}/backgrounds";
        $file = $request->file('background')->storeAs(
            $dir,
            "{$slide->order}_custom." . $request->file('background')->extension(),
            'public'
        );

        $slide->update(['background_image_path' => $file]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'     => true,
                'bg_url' => Storage::url($file),
            ]);
        }

        return back()->with('success', "Imagen del slide #{$slide->order} actualizada.");
    }

    /** Remove background image from a slide (revert to CSS gradient) */
    public function removeBackground(Request $request, CarouselPost $carousel, CarouselSlide $slide): JsonResponse|RedirectResponse
    {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        if ($slide->background_image_path) {
            Storage::disk('public')->delete($slide->background_image_path);
            $slide->update(['background_image_path' => null]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', "Imagen eliminada. Se usará el fondo CSS.");
    }
}
