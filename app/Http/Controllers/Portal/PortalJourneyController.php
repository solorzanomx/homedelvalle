<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\ClientPortalService;
use App\Support\TenantRoadmap;
use Illuminate\Support\Facades\Auth;

/** "Mi camino": la pantalla de inicio del inquilino — el siguiente paso y el camino completo (2026-09-26). */
class PortalJourneyController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function show()
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $rental = $client ? $this->portalService->activeTenantRental($client) : null;

        if (! $rental) {
            return redirect()->route('portal.dashboard');
        }

        $roadmap = TenantRoadmap::build($rental);

        return view('portal.journey', [
            'client' => $client,
            'rental' => $rental,
            'roadmap' => $roadmap,
            'next' => TenantRoadmap::nextAction($rental, $roadmap),
        ]);
    }
}
