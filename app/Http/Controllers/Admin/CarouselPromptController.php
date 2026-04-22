<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\GenerateSlideImageAction;
use App\Http\Controllers\Controller;
use App\Models\CarouselImagePrompt;
use Illuminate\Http\Request;

class CarouselPromptController extends Controller
{
    public function index()
    {
        $prompts = CarouselImagePrompt::loadAll();
        $global  = $prompts->firstWhere('is_global', true);
        $byType  = $prompts->where('is_global', false)->values();

        return view('admin.carousels.prompts', compact('global', 'byType'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'global'           => ['required', 'string', 'max:2000'],
            'prompts'          => ['required', 'array'],
            'prompts.*.key'    => ['required', 'string'],
            'prompts.*.prompt' => ['required', 'string', 'max:2000'],
        ]);

        CarouselImagePrompt::updateOrCreate(
            ['key' => '_global'],
            ['label' => 'Reglas globales', 'prompt' => $request->input('global'), 'is_global' => true]
        );

        foreach ($request->input('prompts') as $item) {
            CarouselImagePrompt::where('key', $item['key'])
                ->where('is_global', false)
                ->update(['prompt' => $item['prompt']]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Prompts actualizados correctamente.');
    }

    public function reset()
    {
        CarouselImagePrompt::truncate();
        CarouselImagePrompt::loadAll();

        return back()->with('success', 'Prompts restaurados a los valores predeterminados.');
    }

    /** Preview the assembled prompt for a given type */
    public function preview(Request $request)
    {
        $request->validate([
            'type'     => ['required', 'string'],
            'headline' => ['nullable', 'string', 'max:255'],
        ]);

        // Build a fake slide to reuse buildPrompt()
        $slide = new \App\Models\CarouselSlide([
            'type'     => $request->input('type'),
            'headline' => $request->input('headline'),
        ]);

        $action = app(GenerateSlideImageAction::class);
        $prompt = $action->buildPrompt($slide);

        return response()->json(['prompt' => $prompt]);
    }
}
