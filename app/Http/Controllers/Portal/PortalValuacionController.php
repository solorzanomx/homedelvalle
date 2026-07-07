<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Services\ClientPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalValuacionController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function show(): View|RedirectResponse
    {
        $client = $this->portalService->getClientForUser(Auth::user());

        if (!$client) {
            return redirect()->route('portal.dashboard');
        }

        // Sin filtrar status (mismo bug ya corregido en otros controllers del
        // portal — ver memoria del proyecto).
        $captacion = Captacion::where('client_id', $client->id)
            ->with(['valuation.adjustments', 'valuation.colonia', 'valuation.snapshot'])
            ->latest()
            ->first();

        if (!$captacion || !$captacion->valuation) {
            return redirect()->route('portal.captacion')
                ->with('info', 'Tu valuación todavía no está lista. Te avisaremos cuando esté disponible.');
        }

        return view('portal.valuacion.show', compact('client', 'captacion'));
    }

    public function confirmPrice(Request $request): RedirectResponse
    {
        $client = $this->portalService->getClientForUser(Auth::user());

        if (!$client) abort(403);

        $captacion = Captacion::where('client_id', $client->id)
            ->latest()
            ->firstOrFail();

        if (!$captacion->precio_acordado || $captacion->etapa3_completed_at) {
            return back();
        }

        $captacion->update(['etapa3_completed_at' => now()]);

        return redirect()->route('portal.valuacion')
            ->with('success', '¡Precio confirmado! Tu asesor comenzará a preparar tu Acuerdo de Representación.');
    }
}
