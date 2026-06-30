<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\RentalProcess;
use App\Services\ClientPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        if (!$client || ($rental->owner_client_id !== $client->id && $rental->tenant_client_id !== $client->id)) {
            abort(403, 'No tienes acceso a este proceso.');
        }

        $role = $rental->owner_client_id === $client->id ? 'propietario' : 'inquilino';

        return view('portal.rentals.show', compact('rental', 'client', 'role'));
    }

    public function investigacion(string $id)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $rental = RentalProcess::with([
            'property', 'ownerClient', 'tenantClient', 'broker',
            'investigation.tenantClient', 'poliza',
        ])->findOrFail($id);

        // Solo el propietario puede ver esto
        if (!$client || $rental->owner_client_id !== $client->id) {
            abort(403, 'No tienes acceso a esta sección.');
        }

        $inv = $rental->investigation;

        // Si el asesor aún no la ha activado, redirigir a la vista principal
        if (!$inv || !$inv->visible_to_owner) {
            return redirect()->route('portal.rentals.show', $id)
                ->with('info', 'La investigación del candidato aún no está disponible.');
        }

        return view('portal.rentals.investigacion', compact('rental', 'client', 'inv'));
    }

    public function submitDecision(Request $request, string $id)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $rental = RentalProcess::with(['investigation', 'ownerClient', 'property'])->findOrFail($id);

        if (!$client || $rental->owner_client_id !== $client->id) {
            abort(403);
        }

        $inv = $rental->investigation;
        if (!$inv || !$inv->visible_to_owner) {
            abort(404);
        }

        $request->validate([
            'owner_decision'       => 'required|in:approved,declined,more_info',
            'owner_decision_notes' => 'nullable|string|max:800',
        ]);

        $decision = $request->owner_decision;

        $inv->update([
            'owner_decision'       => $decision,
            'owner_decision_at'    => now(),
            'owner_decision_notes' => $request->owner_decision_notes,
        ]);

        if ($decision === 'approved') {
            $rental->update(['tenant_approved_at' => now()]);
        }

        // Notificar al asesor asignado
        $assignedUserId = $rental->user_id;
        if ($assignedUserId) {
            $prop    = $rental->property?->address ?? 'el inmueble';
            $owner   = $client->name ?? 'El propietario';
            $labels  = ['approved' => 'aprobó al candidato', 'declined' => 'declinó al candidato', 'more_info' => 'solicita más información sobre el candidato'];
            $label   = $labels[$decision] ?? 'tomó una decisión sobre el candidato';

            Notification::create([
                'user_id' => $assignedUserId,
                'type'    => 'system',
                'title'   => 'Decisión del propietario sobre candidato',
                'body'    => "{$owner} {$label} de {$prop}." . ($request->owner_decision_notes ? ' Nota: ' . $request->owner_decision_notes : ''),
                'data'    => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
            ]);

            // Email al asesor
            try {
                $asesorUser = \App\Models\User::find($assignedUserId);
                if ($asesorUser?->email) {
                    Mail::to($asesorUser->email)
                        ->send(new \App\Mail\Portal\TenantDecisionBrokerMail($rental, $inv, $client));
                }
            } catch (\Exception $e) {
                Log::warning('TenantDecisionBrokerMail failed: ' . $e->getMessage());
            }
        }

        $messages = [
            'approved'  => '¡Candidato aprobado! Tu asesor continuará con el proceso de contrato.',
            'declined'  => 'Hemos notificado a tu asesor. Seguiremos buscando el inquilino ideal.',
            'more_info' => 'Tu asesor recibió tu solicitud y te contactará pronto con más información.',
        ];

        return redirect()->route('portal.rentals.show', $id)
            ->with('success', $messages[$decision]);
    }
}
