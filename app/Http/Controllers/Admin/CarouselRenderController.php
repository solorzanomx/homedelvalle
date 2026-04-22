<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\RenderSlideAction;
use App\Http\Controllers\Controller;
use App\Jobs\RenderCarouselAllJob;
use App\Models\CarouselPost;
use App\Models\CarouselSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CarouselRenderController extends Controller
{
    /** Dispatch background job to render all slides of a carousel */
    public function renderAll(CarouselPost $carousel): RedirectResponse
    {
        if ($carousel->slides()->count() === 0) {
            return back()->with('error', 'Este carrusel no tiene diapositivas para renderizar.');
        }

        // Mark all as pending/queued
        $carousel->slides()->update(['render_status' => 'pending']);

        RenderCarouselAllJob::dispatch($carousel);

        return back()->with('success', 'Renderizado en cola. Las imágenes estarán listas en unos momentos.');
    }

    /** Render a single slide synchronously (for quick re-renders from UI) */
    public function renderSlide(CarouselPost $carousel, CarouselSlide $slide): RedirectResponse
    {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        try {
            app(RenderSlideAction::class)->execute($slide);
            return back()->with('success', "Slide #{$slide->order} renderizado correctamente.");
        } catch (\Throwable $e) {
            return back()->with('error', "Error al renderizar: {$e->getMessage()}");
        }
    }

    /** JSON endpoint: return render status for all slides (for polling) */
    public function status(CarouselPost $carousel): JsonResponse
    {
        $slides = $carousel->slides()
            ->orderBy('order')
            ->get(['id', 'order', 'render_status', 'rendered_image_path', 'render_error']);

        $total  = $slides->count();
        $done   = $slides->where('render_status', 'done')->count();
        $failed = $slides->where('render_status', 'failed')->count();

        return response()->json([
            'total'    => $total,
            'done'     => $done,
            'failed'   => $failed,
            'pending'  => $total - $done - $failed,
            'complete' => ($done + $failed) === $total,
            'slides'   => $slides->map(fn ($s) => [
                'id'            => $s->id,
                'order'         => $s->order,
                'render_status' => $s->render_status,
                'image_url'     => $s->rendered_image_path
                                    ? \Storage::url($s->rendered_image_path)
                                    : null,
                'error'         => $s->render_error,
            ]),
        ]);
    }
}
