<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Models\Client;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class PortalLegalController extends Controller
{
    public function show(Request $request)
    {
        // Share portalClient so layout renders correctly (middleware skips this route)
        $portalClient = Client::where('user_id', Auth::id())->first();
        View::share('portalClient', $portalClient);
        $portalCaptacion = $portalClient
            ? Captacion::where('client_id', $portalClient->id)
                ->with('signatureRequest')
                ->latest()->first()
            : null;
        View::share('portalCaptacion', $portalCaptacion);

        $pendingDocs = LegalDocument::pendingRequiredForPortal(Auth::user()->email);

        // Ya aceptó todo lo requerido → seguir al portal
        if ($pendingDocs->isEmpty()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.terminos', ['pendingDocs' => $pendingDocs]);
    }

    public function aceptar(Request $request)
    {
        $user = Auth::user();
        $pendingDocs = LegalDocument::pendingRequiredForPortal($user->email);

        foreach ($pendingDocs as $doc) {
            LegalAcceptance::record(
                $doc->id,
                $doc->currentVersion->id,
                $user->email,
                $request,
                'portal',
                ['user_id' => $user->id]
            );
        }

        return redirect()->route('portal.dashboard');
    }
}
