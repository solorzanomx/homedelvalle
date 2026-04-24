<?php

namespace App\Http\Middleware;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalLegalAcceptance
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Share the linked client with all portal views
        $portalClient = Client::where('user_id', $user->id)->first();
        View::share('portalClient', $portalClient);

        // Share active captacion for sidebar stage indicators
        $portalCaptacion = $portalClient
            ? Captacion::where('client_id', $portalClient->id)
                ->where('status', 'activo')
                ->with('signatureRequest')
                ->latest()
                ->first()
            : null;
        View::share('portalCaptacion', $portalCaptacion);

        // Find published aviso de privacidad
        $aviso = LegalDocument::where('type', 'aviso_privacidad')
            ->where('status', 'published')
            ->with('currentVersion')
            ->first();

        // If no aviso exists yet, skip the gate
        if (!$aviso || !$aviso->currentVersion) {
            return $next($request);
        }

        $accepted = LegalAcceptance::where('legal_document_id', $aviso->id)
            ->where('email', $user->email)
            ->exists();

        if (!$accepted) {
            View::share('showLegalModal', true);
            View::share('legalAviso', $aviso);
        }

        return $next($request);
    }
}
