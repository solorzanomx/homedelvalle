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

        return view('public.legal', compact('document'));
    }
}
