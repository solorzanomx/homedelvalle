<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ColoniaPage;
use Illuminate\Http\Request;

class ColoniaPageController extends Controller
{
    public function index()
    {
        $colonias = ColoniaPage::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.colonia-pages.index', compact('colonias'));
    }

    public function create()
    {
        return view('admin.colonia-pages.edit', ['colonia' => new ColoniaPage()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $validated['faqs'] = $this->parseFaqs($request);
        ColoniaPage::create($validated);

        return redirect()->route('admin.colonia-pages.index')
            ->with('success', 'Página de colonia creada correctamente.');
    }

    public function edit(ColoniaPage $coloniaPage)
    {
        return view('admin.colonia-pages.edit', ['colonia' => $coloniaPage]);
    }

    public function update(Request $request, ColoniaPage $coloniaPage)
    {
        $validated = $this->validateRequest($request, $coloniaPage->id);
        $validated['faqs'] = $this->parseFaqs($request);
        $coloniaPage->update($validated);

        return redirect()->route('admin.colonia-pages.index')
            ->with('success', 'Página de colonia actualizada correctamente.');
    }

    public function destroy(ColoniaPage $coloniaPage)
    {
        $coloniaPage->delete();
        return redirect()->route('admin.colonia-pages.index')
            ->with('success', 'Página eliminada.');
    }

    private function validateRequest(Request $request, ?int $ignoreId = null): array
    {
        $uniqueSlug = 'required|string|max:100|unique:colonia_pages,slug' . ($ignoreId ? ",{$ignoreId}" : '');

        return $request->validate([
            'slug'                => $uniqueSlug,
            'name'                => 'required|string|max:100',
            'meta_title'          => 'nullable|string|max:255',
            'meta_description'    => 'nullable|string|max:320',
            'heading'             => 'nullable|string|max:255',
            'subheading'          => 'nullable|string|max:500',
            'about'               => 'nullable|string',
            'colony_search_terms' => 'nullable|string|max:500',
            'sort_order'          => 'nullable|integer',
            'is_published'        => 'nullable|boolean',
        ]);
    }

    /**
     * Build the faqs JSON array from repeating form fields.
     * Input: faq_q[] + faq_a[]
     */
    private function parseFaqs(Request $request): array
    {
        $questions = $request->input('faq_q', []);
        $answers   = $request->input('faq_a', []);
        $faqs = [];

        foreach ($questions as $i => $q) {
            $q = trim($q ?? '');
            $a = trim($answers[$i] ?? '');
            if ($q !== '' && $a !== '') {
                $faqs[] = ['q' => $q, 'a' => $a];
            }
        }

        return $faqs;
    }
}
