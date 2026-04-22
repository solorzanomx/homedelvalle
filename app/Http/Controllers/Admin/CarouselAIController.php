<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\GenerateCarouselFromAIAction;
use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use App\Services\CarouselAIService;
use Illuminate\Http\Request;

class CarouselAIController extends Controller
{
    public function __construct(
        private readonly GenerateCarouselFromAIAction $generateAction,
        private readonly CarouselAIService            $aiService,
    ) {}

    /**
     * Show the AI generation form for a carousel.
     */
    public function showForm(CarouselPost $carousel)
    {
        return view('admin.carousels.generate', compact('carousel'));
    }

    /**
     * Trigger full AI generation (slides + captions + hashtags).
     */
    public function generate(Request $request, CarouselPost $carousel)
    {
        if (!$carousel->isEditable()) {
            return back()->with('error', 'Solo se puede generar en carruseles con estado Borrador o En revisión.');
        }

        $useWebSearch = $request->boolean('use_web_search', false);

        try {
            $this->generateAction->execute($carousel, $useWebSearch);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.carousels.show', $carousel)
            ->with('success', 'Carrusel generado correctamente. Revisa las diapositivas.');
    }

    /**
     * Regenerate only the caption + hashtags.
     */
    public function regenerateCaption(Request $request, CarouselPost $carousel)
    {
        try {
            $result = $this->aiService->regenerateCaption($carousel);
            $carousel->update($result);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al regenerar caption: ' . $e->getMessage());
        }

        return back()->with('success', 'Caption regenerado correctamente.');
    }
}
