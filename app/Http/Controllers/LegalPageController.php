<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;

class LegalPageController extends Controller
{
    /**
     * Display a published legal document by its slug.
     */
    public function show(string $slug)
    {
        $document = LegalDocument::published()
            ->where('slug', $slug)
            ->where('is_public', true)
            ->with('currentVersion')
            ->firstOrFail();

        // ?embed=1 → bare HTML for iframe use (no layout, no header/footer)
        if (request()->boolean('embed')) {
            return response()->view('public.legal-embed', compact('document'))
                ->header('X-Frame-Options', 'SAMEORIGIN');
        }

        return view('public.legal', compact('document'));
    }
}
