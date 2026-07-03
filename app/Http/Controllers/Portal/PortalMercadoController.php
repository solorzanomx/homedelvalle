<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Models\PropertyMarketingStrategy;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Auth;

class PortalMercadoController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function show()
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) {
            return redirect()->route('portal.dashboard');
        }

        $captacion = Captacion::where('client_id', $client->id)->latest()->first();
        $ventaOperation = $captacion?->operation
            ?->spawnedOperations()->where('type', 'venta')->with('property.photos')->latest()->first();

        if (!$ventaOperation) {
            return redirect()->route('portal.dashboard');
        }

        $property = $ventaOperation->property;
        $stage    = $ventaOperation->stage;

        $strategy = PropertyMarketingStrategy::where('operation_id', $ventaOperation->id)
            ->whereNotNull('approved_at')
            ->first();

        $photosCount = $property?->photos->count() ?? 0;
        $hasVideo    = (bool) $property?->youtube_url;

        // Publicación: analítica de vistas del sitio (últimos 30 días) —
        // reusa Property::views() ya construido para /properties/analytics.
        $viewsTotal  = null;
        $viewsUnique = null;
        if ($property && in_array($stage, ['publicacion', 'candidatos', 'oferta_aceptada'])) {
            $since       = now()->subDays(30);
            $viewsTotal  = $property->views()->where('viewed_at', '>=', $since)->count();
            $viewsUnique = $property->views()->where('viewed_at', '>=', $since)->distinct('visitor_key')->count('visitor_key');
        }

        // Candidatos: ofertas pendientes de este mismo proceso de venta.
        $pendingOffersCount = null;
        $acceptedOffer      = null;
        if ($stage === 'candidatos') {
            $pendingOffersCount = $ventaOperation->purchaseOffers()->where('status', 'pending')->count();
        } elseif ($stage === 'oferta_aceptada') {
            $acceptedOffer = $ventaOperation->purchaseOffers()->where('status', 'accepted')->latest()->first();
        }

        return view('portal.mercado', compact(
            'client', 'property', 'ventaOperation', 'stage', 'strategy',
            'photosCount', 'hasVideo', 'viewsTotal', 'viewsUnique',
            'pendingOffersCount', 'acceptedOffer'
        ));
    }
}
