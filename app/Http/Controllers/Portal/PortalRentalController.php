<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\RentalProcess;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Auth;

class PortalRentalController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function index()
    {
        $client = $this->portalService->getClientForUser(Auth::user());

        if (!$client) {
            return view('portal.rentals.index', ['rentals' => collect(), 'client' => null]);
        }

        $rentals = $this->portalService->getRentalsForClient($client);

        return view('portal.rentals.index', compact('rentals', 'client'));
    }

    public function show(string $id)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $rental = RentalProcess::with([
            'property', 'ownerClient', 'tenantClient', 'broker',
            'documents', 'contracts.template', 'stageLogs',
        ])->findOrFail($id);

        // Verify the client is related to this rental
        if (!$client || ($rental->owner_client_id !== $client->id && $rental->tenant_client_id !== $client->id)) {
            abort(403, 'No tienes acceso a este proceso.');
        }

        $role = $rental->owner_client_id === $client->id ? 'propietario' : 'inquilino';

        return view('portal.rentals.show', compact('rental', 'client', 'role'));
    }
}
