<?php

namespace App\Services;

use App\Models\Client;
use App\Models\PortalAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientPortalService
{
    // Interest types that trigger an automatic captación pipeline
    private const VENTA_TYPES = ['venta', 'venta_propietario', 'vendedor', 'propietario'];

    public function __construct(private CaptacionService $captacion) {}

    /**
     * Create a portal user account for a client.
     * Returns the created User and the plain-text password (if generated).
     * Also auto-activates the captación pipeline for venta clients.
     */
    public function createPortalAccount(Client $client, ?string $password = null): array
    {
        if ($client->user_id) {
            $this->maybeActivateCaptacion($client);
            return ['user' => User::find($client->user_id), 'password' => null];
        }

        // Si ya existe un usuario con ese email (huérfano de cliente borrado), reutilizarlo
        $existing = User::where('email', $client->email)->first();
        if ($existing) {
            $plain = $password ?: Str::random(10);
            $existing->update([
                'name'     => $client->name,
                'password' => $plain,
                'phone'    => $client->phone,
                'role'     => 'client',
            ]);
            $client->update(['user_id' => $existing->id]);
            $this->maybeActivateCaptacion($client);
            return ['user' => $existing, 'password' => $plain];
        }

        $plainPassword = $password ?: Str::random(10);

        $user = User::create([
            'name'     => $client->name,
            'email'    => $client->email,
            'password' => $plainPassword,
            'phone'    => $client->phone,
            'role'     => 'client',
        ]);

        $client->update(['user_id' => $user->id]);

        $this->maybeActivateCaptacion($client);

        return ['user' => $user, 'password' => $plainPassword];
    }

    /**
     * If the client has a venta interest type, ensure a captación pipeline exists.
     */
    private function maybeActivateCaptacion(Client $client): void
    {
        $types = $client->interest_types ?? [];
        if (empty($types) || !array_intersect(self::VENTA_TYPES, $types)) {
            return;
        }

        try {
            $this->captacion->getOrCreateForClient($client);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ClientPortalService: no se pudo crear captación', [
                'client_id' => $client->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the Client record linked to a portal user.
     */
    public function getClientForUser(User $user): ?Client
    {
        return Client::where('user_id', $user->id)->first();
    }

    /**
     * Get rental processes where client is owner or tenant.
     */
    public function getRentalsForClient(Client $client)
    {
        return \App\Models\RentalProcess::where('owner_client_id', $client->id)
            ->orWhere('tenant_client_id', $client->id)
            ->with(['property', 'ownerClient', 'tenantClient'])
            ->latest()
            ->get();
    }

    /**
     * Get documents related to a client's rental processes.
     */
    public function getDocumentsForClient(Client $client)
    {
        $rentalIds = \App\Models\RentalProcess::where('owner_client_id', $client->id)
            ->orWhere('tenant_client_id', $client->id)
            ->pluck('id');

        return \App\Models\Document::where('client_id', $client->id)
            ->orWhereIn('rental_process_id', $rentalIds)
            ->with(['rentalProcess', 'uploader'])
            ->latest()
            ->get();
    }

    // ── Invitación de portal ─────────────────────────────────────────────────

    /**
     * Genera un token de invitación con TTL de 7 días para activar la cuenta.
     * Almacena en cache (array de IDs, no objeto Eloquent).
     */
    public function generateInvitationToken(User $user): string
    {
        $token = Str::random(64);
        // Cache: sólo IDs — nunca objetos Eloquent
        Cache::put("portal_invitation:{$token}", ['user_id' => $user->id], now()->addDays(7));
        return $token;
    }

    /**
     * Devuelve el User vinculado al token de invitación, o null si inválido/expirado.
     */
    public function getUserByInvitationToken(string $token): ?User
    {
        $data = Cache::get("portal_invitation:{$token}");
        if (! $data || empty($data['user_id'])) {
            return null;
        }
        return User::find($data['user_id']);
    }

    /**
     * Acepta la invitación: establece la contraseña y activa la cuenta.
     * Invalida el token tras el uso.
     */
    public function acceptInvitation(string $token, string $password): ?User
    {
        $user = $this->getUserByInvitationToken($token);
        if (! $user) {
            return null;
        }

        $user->forceFill([
            'password'   => $password,
            'is_active'  => true,
        ])->save();

        Cache::forget("portal_invitation:{$token}");

        return $user;
    }

    /**
     * Envía el email de bienvenida al portal con el enlace de activación.
     * Usa PHPMailer + tabla email_settings (convención del proyecto).
     */
    public function sendWelcomeInvitation(User $user): void
    {
        $token = $this->generateInvitationToken($user);
        $activationUrl = "https://miportal.homedelvalle.mx/activar/{$token}";

        try {
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendTemplated('portal_welcome', $user->email, [
                'Nombre'          => $user->name,
                'ActivationLink'  => $activationUrl,
                'Email'           => $user->email,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('ClientPortalService: no se pudo enviar portal_welcome', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // ── Impersonación admin → cliente ────────────────────────────────────────

    /**
     * Inicia una sesión de impersonación: el admin ve el portal como el cliente.
     * Guarda el user_id original en sesión para poder restaurarlo.
     */
    public function impersonate(User $admin, User $clientUser, ?Request $request = null): void
    {
        session(['impersonating_as' => $clientUser->id, 'original_user_id' => $admin->id]);

        \Illuminate\Support\Facades\Auth::login($clientUser);

        $this->logAudit($admin, 'impersonate_start', 'User', $clientUser->id, $request, [
            'impersonated_user_id' => $clientUser->id,
            'impersonated_name'    => $clientUser->name,
        ]);
    }

    /**
     * Termina la impersonación y restaura la sesión del admin.
     */
    public function endImpersonation(?Request $request = null): void
    {
        $originalId = session('original_user_id');
        $impersonatedId = session('impersonating_as');

        session()->forget(['impersonating_as', 'original_user_id']);

        if ($originalId) {
            $admin = User::find($originalId);
            if ($admin) {
                \Illuminate\Support\Facades\Auth::login($admin);
                $this->logAudit($admin, 'impersonate_end', 'User', $impersonatedId, $request);
            }
        }
    }

    // ── Audit log ────────────────────────────────────────────────────────────

    /**
     * Registra una acción del portal en portal_audit_logs.
     * Solo arrays/IDs en cache — nunca objetos Eloquent (convención del proyecto).
     */
    public function logAudit(
        User $user,
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?Request $request = null,
        array $metadata = []
    ): void {
        try {
            // Obtener client_id si el user es un cliente
            $clientId = null;
            if ($user->role === 'client') {
                $client = Client::where('user_id', $user->id)->select('id')->first();
                $clientId = $client?->id;
            }

            \Illuminate\Support\Facades\DB::table('portal_audit_logs')->insert([
                'user_id'     => $user->id,
                'client_id'   => $clientId,
                'action'      => $action,
                'target_type' => $targetType,
                'target_id'   => $targetId,
                'ip'          => $request?->ip(),
                'user_agent'  => substr($request?->userAgent() ?? '', 0, 300),
                'metadata'    => $metadata ? json_encode($metadata) : null,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit log nunca debe romper el flujo principal
            \Illuminate\Support\Facades\Log::warning('ClientPortalService: audit log falló', [
                'action' => $action,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
