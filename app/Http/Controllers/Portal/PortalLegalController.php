<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
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
        View::share('portalClient', Client::where('user_id', Auth::id())->first());

        $aviso = LegalDocument::where('type', 'aviso_privacidad')
            ->where('status', 'published')
            ->with('currentVersion')
            ->first();

        // If already accepted, go to dashboard
        if ($aviso) {
            $accepted = LegalAcceptance::where('legal_document_id', $aviso->id)
                ->where('email', Auth::user()->email)
                ->exists();

            if ($accepted) {
                return redirect()->route('portal.dashboard');
            }
        }

        return view('portal.terminos', compact('aviso'));
    }

    public function aceptar(Request $request)
    {
        $aviso = LegalDocument::where('type', 'aviso_privacidad')
            ->where('status', 'published')
            ->with('currentVersion')
            ->first();

        if ($aviso && $aviso->currentVersion) {
            LegalAcceptance::record(
                $aviso->id,
                $aviso->currentVersion->id,
                Auth::user()->email,
                $request,
                'portal',
                ['user_id' => Auth::id()]
            );
        }

        return redirect()->route('portal.dashboard');
    }
}
