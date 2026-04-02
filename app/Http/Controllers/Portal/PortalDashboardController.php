<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Services\ClientPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalDashboardController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function index()
    {
        $client = $this->portalService->getClientForUser(Auth::user());

        if (!$client) {
            return view('portal.dashboard', ['client' => null, 'rentals' => collect(), 'documents' => collect(), 'contracts' => collect()]);
        }

        $rentals = $this->portalService->getRentalsForClient($client);
        $documents = $this->portalService->getDocumentsForClient($client);

        $rentalIds = $rentals->pluck('id');
        $contracts = \App\Models\Contract::whereIn('rental_process_id', $rentalIds)
            ->with('rentalProcess')
            ->latest()
            ->get();

        return view('portal.dashboard', compact('client', 'rentals', 'documents', 'contracts'));
    }

    public function account()
    {
        return view('portal.account');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contrasena actual no es correcta.']);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Contrasena actualizada correctamente.');
    }
}
