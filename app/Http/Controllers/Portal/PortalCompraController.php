<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Auth;

class PortalCompraController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function show()
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        if (!$client) {
            return redirect()->route('portal.dashboard');
        }

        $operation = Operation::where('secondary_client_id', $client->id)
            ->orWhereHas('purchaseOffers', fn ($q) => $q->where('client_id', $client->id))
            ->with(['property', 'purchaseOffers' => fn ($q) => $q->where('client_id', $client->id)])
            ->latest()
            ->first();

        if (!$operation) {
            return redirect()->route('portal.dashboard');
        }

        $property = $operation->property;
        $stage    = $operation->stage;
        $offer    = $operation->purchaseOffers->sortByDesc('offered_at')->first();

        return view('portal.mi-compra', compact('client', 'property', 'operation', 'stage', 'offer'));
    }
}
