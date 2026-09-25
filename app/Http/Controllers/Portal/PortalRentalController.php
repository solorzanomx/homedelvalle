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
            'property.photos', 'ownerClient', 'tenantClient', 'broker',
            'documents', 'contracts.template', 'stageLogs',
        ])->findOrFail($id);

        if (!$client || ($rental->owner_client_id !== $client->id && $rental->tenant_client_id !== $client->id)) {
            abort(403, 'No tienes acceso a este proceso.');
        }

        $role = $rental->owner_client_id === $client->id ? 'propietario' : 'inquilino';

        return view('portal.rentals.show', compact('rental', 'client', 'role'));
    }

    /** Resuelve el trato del que el cliente autenticado es el INQUILINO (nunca el propietario). */
    private function tenantRental(string $id): array
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $rental = RentalProcess::with(['tenantClient', 'poliza', 'property'])->findOrFail($id);

        if (! $client || $rental->tenant_client_id !== $client->id) {
            abort(403, 'Solo el inquilino puede hacer esto.');
        }

        return [$client, $rental];
    }

    private function notifyBroker(RentalProcess $rental, string $title, string $body): void
    {
        $userId = $rental->broker_id ?? $rental->user_id;
        if (! $userId) {
            return;
        }
        Notification::create([
            'user_id' => $userId,
            'type' => 'garantia_inquilino',
            'title' => $title,
            'body' => $body,
            'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
        ]);
    }

    /** El inquilino declara si tiene aval con inmueble en CDMX. Sin aval → póliza, definitivamente. */
    public function declareGuarantee(Request $request, string $id)
    {
        [$client, $rental] = $this->tenantRental($id);
        $request->validate(['has_aval' => 'required|in:0,1']);

        $hasAval = $request->input('has_aval') === '1';
        $update = ['tenant_has_aval' => $hasAval, 'guarantee_declared_at' => now()];

        if (! $hasAval) {
            $update['guarantee_type'] = 'poliza_juridica';
        } elseif (in_array($rental->guarantee_type, ['deposito', 'poliza_juridica', 'fianza', null], true)) {
            $update['guarantee_type'] = 'aval';
        }
        $rental->update($update);

        // Sin aval en CDMX = póliza: al PROPIETARIO le toca elegir el plan y el reparto (se le avisa por portal y correo).
        if (! $hasAval) {
            app(\App\Services\PolizaDecisionService::class)->askOwnerToDecide($rental->fresh(['ownerClient']));
        }

        $this->notifyBroker($rental, 'El inquilino definió su garantía',
            "{$client->name} indicó que " . ($hasAval ? 'SÍ tiene aval con inmueble en CDMX (sigue la investigación de aval)' : 'NO tiene aval en CDMX (va con póliza jurídica)') . " en la renta #{$rental->id}.");

        return back()->with('success', $hasAval
            ? 'Listo. Seguiremos con la investigación de tu aval; completa sus datos y documentos.'
            : 'Listo. Sin aval en CDMX tu garantía es una póliza jurídica: tu propietario elegirá el plan y te mostraremos lo que te toca.');
    }

    /**
     * El PROPIETARIO decide la póliza (plan) y cómo se reparte el costo (inquilino 100% o 50/50). El inquilino solo lo ve.
     * Solo el propietario de esa renta (403 para cualquier otro cliente).
     */
    public function decidePolicy(Request $request, string $id, \App\Services\PolizaDecisionService $decisions)
    {
        $client = $this->portalService->getClientForUser(Auth::user());
        $rental = RentalProcess::with(['poliza', 'ownerClient', 'tenantClient'])->findOrFail($id);
        abort_unless($client && $rental->owner_client_id === $client->id, 403, 'Solo el propietario puede elegir la póliza.');

        $data = $request->validate([
            'plan_id' => 'required|integer',
            'tenant_share' => 'required|in:' . implode(',', array_keys(\App\Support\PolizaPricing::SHARE_OPTIONS)),
        ]);

        $plan = \App\Models\PolizaPlan::offered()->find($data['plan_id']);
        if (! $plan) {
            return back()->with('error', 'Ese plan ya no está disponible. Elige otro.');
        }

        try {
            $result = $decisions->decide($rental, $plan, (int) $data['tenant_share'], 'owner', Auth::id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        $reparto = $result['split']['tenant_pct'] === 100 ? 'la paga tu inquilino' : 'se reparte mitad y mitad';
        return back()->with('success', "Elegiste el plan {$plan->name} ($" . number_format($result['amount']) . "); {$reparto}. Tu asesor tramitará el alta.");
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
