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
        // ?embed=1 lo usa el modal de aceptación del Portal (layouts/portal.blade.php)
        // para mostrar documentos requeridos que no necesariamente son públicos
        // (ej. Acuerdo de Confidencialidad) — is_public solo controla si el
        // documento tiene página propia de marketing en /legal/slug, no si un
        // cliente autenticado puede verlo para aceptarlo. Exigirlo aquí también
        // tumbaba el iframe con 404 para cualquier documento no marcado público.
        $isEmbed = request()->boolean('embed');

        $query = LegalDocument::published()->where('slug', $slug);
        if (! $isEmbed) {
            $query->where('is_public', true);
        }
        $document = $query->with('currentVersion')->firstOrFail();

        // ?embed=1 → bare HTML for iframe use (no layout, no header/footer)
        if ($isEmbed) {
            return response()->view('public.legal-embed', compact('document'))
                ->header('X-Frame-Options', 'SAMEORIGIN');
        }

        return view('public.legal', compact('document'));
    }
}
