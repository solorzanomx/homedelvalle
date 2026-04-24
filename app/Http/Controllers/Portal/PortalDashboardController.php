<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalDashboardController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function index()
    {
        $client = $this->portalService->getClientForUser(Auth::user());

        if (!$client) {
            return view('portal.dashboard', [
                'client'     => null,
                'rentals'    => collect(),
                'documents'  => collect(),
                'contracts'  => collect(),
                'properties' => collect(),
            ]);
        }

        $interests   = $client->interest_types ?? [];
        $isRental    = (bool) array_intersect(['renta_propietario', 'renta_inquilino'], $interests);
        $isVenta     = in_array('venta', $interests);

        $rentals   = $isRental ? $this->portalService->getRentalsForClient($client) : collect();
        $documents = $this->portalService->getDocumentsForClient($client);

        $rentalIds = $rentals->pluck('id');
        $contracts = \App\Models\Contract::whereIn('rental_process_id', $rentalIds)
            ->with('rentalProcess')
            ->latest()
            ->get();

        // Properties owned by the client (relevant for venta/captación)
        $properties = $isVenta
            ? Property::where('client_id', $client->id)->latest()->get()
            : collect();

        return view('portal.dashboard', compact('client', 'rentals', 'documents', 'contracts', 'properties', 'isRental', 'isVenta'));
    }

    public function account()
    {
        return view('portal.account');
    }

    public function updatePassword(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
