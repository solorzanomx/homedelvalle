<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Carousel\BatchGenerateCarouselsAction;
use App\Actions\Carousel\RunTopicDiscoveryAction;
use App\Http\Controllers\Controller;
use App\Models\CarouselTopicSuggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CarouselDiscoveryController extends Controller
{
    /** Show the discovery form */
    public function form(): View
    {
        return view('admin.carousels.discovery.form');
    }

    /** Run discovery, persist suggestions, redirect to review screen */
    public function discover(Request $request, RunTopicDiscoveryAction $action): RedirectResponse
    {
        $request->validate([
            'sources'    => ['required', 'array', 'min:1'],
            'sources.*'  => ['in:web,blog,manual'],
            'free_text'  => ['nullable', 'string', 'max:500'],
        ]);

        $sources   = $request->input('sources', ['web']);
        $freeText  = (string) $request->input('free_text', '');
        $userId    = $request->user()->id;

        $result    = $action->execute($sources, $freeText, $userId);

        if ($result['count'] === 0) {
            return back()
                ->withInput()
                ->with('error', 'No se encontraron temas. Verifica que ANTHROPIC_API_KEY y PERPLEXITY_API_KEY estén configurados en .env, o añade un tema libre.');
        }

        return redirect()->route('admin.carousels.discovery.review', $result['session_id'])
                         ->with('success', "{$result['count']} temas descubiertos. Selecciona los que quieres generar.");
    }

    /** Show suggestions for a discovery session */
    public function review(string $session): View|RedirectResponse
    {
        $suggestions = CarouselTopicSuggestion::where('session_id', $session)
            ->orderBy('priority')
            ->get();

        if ($suggestions->isEmpty()) {
            return redirect()->route('admin.carousels.discovery.form')
                ->with('error', 'No se encontraron sugerencias para esta sesión. Intenta de nuevo.');
        }

        $sourcesUsed = $suggestions->pluck('source')->unique()->values();
        $createdAt   = $suggestions->first()->created_at;

        return view('admin.carousels.discovery.review', compact(
            'suggestions', 'session', 'sourcesUsed', 'createdAt'
        ));
    }

    /** Batch-generate carousels from selected suggestions */
    public function generate(Request $request, string $session, BatchGenerateCarouselsAction $action): RedirectResponse
    {
        $request->validate([
            'suggestion_ids'   => ['required', 'array', 'min:1', 'max:10'],
            'suggestion_ids.*' => ['integer', 'exists:carousel_topic_suggestions,id'],
        ]);

        $ids     = $request->input('suggestion_ids');
        $userId  = $request->user()->id;
        $created = $action->execute($ids, $userId);

        $count = $created->count();

        if ($count === 0) {
            return back()->with('error', 'No se pudo generar ningún carrusel. Revisa los logs.');
        }

        return redirect()
            ->route('admin.carousels.index')
            ->with('success', "Se generaron {$count} carrusel(es). Las imágenes se están renderizando en segundo plano.");
    }
}
