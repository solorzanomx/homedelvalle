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

        // Inquilino con una renta activa: su "inicio" ES "Mi camino" (una sola pantalla que dice qué sigue).
        if ($this->portalService->activeTenantRental($client)) {
            return redirect()->route('portal.journey');
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

        // Captacion del cliente (sin filtrar status: una vez completada sigue
        // siendo la fuente de $ventaOperation — mismo bug ya corregido en
        // EnsurePortalLegalAcceptance/PortalLegalController, ver memoria del
        // proyecto).
        $captacion = $isVenta
            ? \App\Models\Captacion::where('client_id', $client->id)
                ->with(['valuation', 'signatureRequest', 'documents'])
                ->latest()
                ->first()
            : null;

        $docsApproved = $captacion?->documents->where('captacion_status', 'aprobado')->count() ?? 0;
        $docsPending  = $captacion?->documents->where('captacion_status', 'pendiente')->count() ?? 0;
        $docsTotal    = $captacion?->documents->count() ?? 0;

        // Operation de venta (post-exclusiva) con su historial de etapas, para
        // la línea de tiempo "qué hemos logrado" del dashboard.
        $ventaOperation = $captacion?->operation
            ?->spawnedOperations()->where('type', 'venta')->with('stageLogs')->latest()->first();

        return view('portal.dashboard', compact(
            'client', 'rentals', 'documents', 'contracts', 'properties',
            'isRental', 'isVenta', 'captacion', 'docsApproved', 'docsPending', 'docsTotal',
            'ventaOperation'
        ));
    }

    public function account()
    {
        $user = Auth::user();
        $notifPrefs = \App\Models\PortalNotificationPreference::forUser($user->id);

        // Los toggles de "visita agendada/confirmada/reagendada" solo
        // aplican a quien tiene un inmueble propio recibiendo visitas
        // (VisitResponseController solo le manda esos correos al
        // property->owner) — mostrárselos a un arrendatario o comprador
        // no tenía sentido, esos correos nunca le iban a llegar.
        $client = $this->portalService->getClientForUser($user);
        $interests = $client?->interest_types ?? [];
        $isPropietario = !empty(array_intersect(['venta', 'renta_propietario'], $interests));

        return view('portal.account', compact('user', 'notifPrefs', 'isPropietario'));
    }

    public function updateNotifications(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        \App\Models\PortalNotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'notify_visit_scheduled'  => $request->boolean('notify_visit_scheduled'),
                'notify_visit_confirmed'  => $request->boolean('notify_visit_confirmed'),
                'notify_visit_rescheduled'=> $request->boolean('notify_visit_rescheduled'),
                'notify_process_updates'  => $request->boolean('notify_process_updates'),
                'summary_frequency'       => $request->input('summary_frequency', 'none'),
            ]
        );
        return back()->with('success', 'Preferencias guardadas.');
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
