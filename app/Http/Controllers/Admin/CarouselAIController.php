<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\GenerateCarouselFromAIAction;
use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use App\Services\CarouselAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarouselAIController extends Controller
{
    public function __construct(
        private readonly GenerateCarouselFromAIAction $generateAction,
        private readonly CarouselAIService            $aiService,
    ) {}

    /** Show the AI generation form for a carousel. */
    public function showForm(CarouselPost $carousel)
    {
        $templates = \App\Models\CarouselTemplate::active()->get();
        return view('admin.carousels.generate', compact('carousel', 'templates'));
    }

    /** Trigger full AI generation (slides + captions + hashtags). */
    public function generate(Request $request, CarouselPost $carousel): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (!$carousel->isEditable()) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Solo se puede generar en carruseles Borrador o En revisión.'], 422);
            }
            return back()->with('error', 'Solo se puede generar en carruseles con estado Borrador o En revisión.');
        }

        // Save template selection before generating (affects image composition prompts)
        if ($request->filled('template_id')) {
            $carousel->update(['template_id' => $request->integer('template_id')]);
            $carousel->refresh();
        }

        $useWebSearch = $request->boolean('use_web_search', false);

        try {
            $this->generateAction->execute($carousel, $useWebSearch);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Error al generar: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al generar: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            $carousel->load('slides');
            return response()->json([
                'ok'        => true,
                'message'   => 'Carrusel generado correctamente.',
                'redirect'  => route('admin.carousels.show', $carousel),
                'autoImages'=> true,
            ]);
        }

        return redirect()
            ->route('admin.carousels.show', $carousel)
            ->with('success', 'Carrusel generado correctamente. Revisa las diapositivas.')
            ->with('auto_images', true);
    }

    /** Regenerate only the caption + hashtags. */
    public function regenerateCaption(Request $request, CarouselPost $carousel): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $result = $this->aiService->regenerateCaption($carousel);
            $carousel->update($result);
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al regenerar caption: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok'            => true,
                'caption_short' => $carousel->fresh()->caption_short,
                'caption_long'  => $carousel->fresh()->caption_long,
                'hashtags'      => $carousel->fresh()->hashtags ?? [],
            ]);
        }

        return back()->with('success', 'Caption regenerado correctamente.');
    }
}
