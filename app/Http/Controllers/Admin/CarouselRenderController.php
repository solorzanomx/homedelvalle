<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\RenderSlideAction;
use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use App\Models\CarouselSlide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class CarouselRenderController extends Controller
{
    /** Render all slides of a carousel synchronously */
    public function renderAll(Request $request, CarouselPost $carousel, RenderSlideAction $action): JsonResponse|RedirectResponse
    {
        $slides = $carousel->slides()->orderBy('order')->get();

        if ($slides->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'No hay diapositivas para renderizar.'], 422);
            }
            return back()->with('error', 'Este carrusel no tiene diapositivas para renderizar.');
        }

        $done = 0; $failed = 0;
        foreach ($slides as $slide) {
            try {
                $action->execute($slide);
                $done++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        if ($request->expectsJson()) {
            // Return updated slide statuses so the UI can refresh immediately
            $updated = $carousel->slides()->orderBy('order')
                ->get(['id', 'order', 'render_status', 'rendered_image_path', 'render_error']);
            return response()->json([
                'ok'     => true,
                'done'   => $done,
                'failed' => $failed,
                'slides' => $updated->map(fn($s) => [
                    'id'           => $s->id,
                    'render_status'=> $s->render_status,
                    'image_url'    => $s->rendered_image_path ? ('/storage/' . $s->rendered_image_path) : null,
                    'error'        => $s->render_error,
                ]),
            ]);
        }

        return back()->with('success', "{$done} slides renderizados." . ($failed ? " {$failed} fallaron." : ''));
    }

    /** Render a single slide synchronously */
    public function renderSlide(Request $request, CarouselPost $carousel, CarouselSlide $slide, RenderSlideAction $action): JsonResponse|RedirectResponse
    {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        try {
            $action->execute($slide);
            $fresh = $slide->fresh();
            if ($request->expectsJson()) {
                return response()->json([
                    'ok'           => true,
                    'render_status'=> $fresh->render_status,
                    'image_url'    => $fresh->rendered_image_path ? ('/storage/' . $fresh->rendered_image_path) : null,
                ]);
            }
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error al renderizar: ' . $e->getMessage());
        }

        return back()->with('success', "Slide #{$slide->order} renderizado.");
    }

    /** Clear rendered image for a single slide so it can be re-rendered with a different template */
    public function clearRender(Request $request, CarouselPost $carousel, CarouselSlide $slide): JsonResponse|RedirectResponse
    {
        abort_if($slide->carousel_post_id !== $carousel->id, 404);

        if ($slide->rendered_image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($slide->rendered_image_path);
        }

        $slide->update([
            'rendered_image_path' => null,
            'render_status'       => 'pending',
            'render_error'        => null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', "Render del slide #{$slide->order} eliminado.");
    }

    /** Download all rendered slides as a ZIP for manual Instagram posting */
    public function downloadSlides(CarouselPost $carousel): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        $slides = $carousel->slides()
            ->orderBy('order')
            ->whereNotNull('rendered_image_path')
            ->get();

        if ($slides->isEmpty()) {
            return redirect()
                ->route('admin.carousels.show', $carousel)
                ->with('error', 'No hay slides renderizados para descargar.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'hdv_carousel_') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($slides as $slide) {
            $filePath = Storage::disk('public')->path($slide->rendered_image_path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, "slide_{$slide->order}.png");
            }
        }

        $zip->close();

        $slug     = Str::slug($carousel->title, '_');
        $filename = "carrusel_{$carousel->id}_{$slug}.zip";

        return response()->download($zipPath, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /** JSON endpoint: return render status for all slides (for polling) */
    public function status(CarouselPost $carousel): JsonResponse
    {
        $slides = $carousel->slides()
            ->orderBy('order')
            ->get(['id', 'order', 'render_status', 'rendered_image_path', 'background_image_path', 'render_error']);

        $total  = $slides->count();
        $done   = $slides->where('render_status', 'done')->count();
        $failed = $slides->where('render_status', 'failed')->count();

        return response()->json([
            'total'    => $total,
            'done'     => $done,
            'failed'   => $failed,
            'pending'  => $total - $done - $failed,
            'complete' => $total > 0 && ($done + $failed) === $total,
            'slides'   => $slides->map(fn ($s) => [
                'id'            => $s->id,
                'order'         => $s->order,
                'render_status' => $s->render_status,
                'image_url'     => $s->rendered_image_path ? ('/storage/' . $s->rendered_image_path) : null,
                'bg_url'        => $s->background_image_path ? ('/storage/' . $s->background_image_path) : null,
                'error'         => $s->render_error,
            ]),
        ]);
    }
}
