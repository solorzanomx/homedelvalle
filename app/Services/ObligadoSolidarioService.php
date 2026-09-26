<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\Notification;
use App\Models\RentalProcess;
use App\Models\User;
use App\Support\ExpedienteFields;
use App\Support\RentalExpedienteStatus;
use App\Support\TenantDocumentRows;
use App\Support\TenantRoadmap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Obligado solidario (2026-09-28): cuando el inquilino NO tiene aval en CDMX (garantía = póliza) se le pide una persona
 * que responde con él y aporta LOS MISMOS datos y documentos: datos personales, identificación y domicilio,
 * trabajo/ingresos y sus 3 documentos (INE o pasaporte, domicilio, ingresos de los últimos 3 meses).
 *
 * Es un Client con SU PROPIO Portal (invitación por correo): captura y sube él mismo; el inquilino solo ve el avance
 * (nunca sus datos ni documentos). El asesor puede exentarlo por trato (`obligado_required = false`).
 */
class ObligadoSolidarioService
{
    /** ¿Este trato exige obligado solidario? Póliza (sin aval) y no exentado por el asesor. */
    public function isRequired(RentalProcess $rental): bool
    {
        return TenantRoadmap::route($rental) === TenantRoadmap::ROUTE_POLIZA && $rental->obligado_required !== false;
    }

    /**
     * Registra (o reemplaza) al obligado solidario y le manda la invitación a su Portal.
     *
     * @param  array{name:string,email:string,phone:string,relationship?:?string}  $data
     * @throws \DomainException con un mensaje apto para mostrar
     */
    public function register(RentalProcess $rental, array $data, string $by = 'tenant'): Client
    {
        $email = mb_strtolower(trim($data['email']));
        $rental->loadMissing(['tenantClient', 'ownerClient']);

        if ($rental->tenantClient?->email && mb_strtolower($rental->tenantClient->email) === $email) {
            throw new \DomainException('El obligado solidario no puede ser el mismo inquilino: escribe el correo de otra persona.');
        }
        if ($rental->ownerClient?->email && mb_strtolower($rental->ownerClient->email) === $email) {
            throw new \DomainException('El obligado solidario no puede ser el propietario.');
        }
        // Cambiar a otra persona solo mientras la actual no haya empezado (no se descartan documentos ya subidos).
        if ($rental->obligado_client_id) {
            $current = $rental->obligado;
            if ($current && mb_strtolower((string) $current->email) !== $email && $this->hasStarted($rental)) {
                throw new \DomainException('Tu obligado solidario actual ya empezó a subir sus documentos. Para cambiarlo, habla con tu asesor.');
            }
        }

        // SEGURIDAD: nunca se toca una cuenta interna. (ClientPortalService::createPortalAccount reutiliza al usuario
        // con ese correo y le cambia rol y contraseña: aquí NO se usa.)
        $user = User::where('email', $email)->first();
        if ($user && $user->role !== 'client') {
            throw new \DomainException('Ese correo pertenece a otra cuenta. Escribe un correo distinto.');
        }

        $client = $user ? Client::where('user_id', $user->id)->first() : Client::where('email', $email)->first();
        if ($client && in_array($client->id, array_filter([$rental->tenant_client_id, $rental->owner_client_id]), true)) {
            throw new \DomainException('Esa persona ya participa en este trato con otro papel. Elige a otra.');
        }

        $advisorId = $rental->broker_id ?? $rental->user_id ?? $rental->tenantClient?->assigned_user_id;
        if (! $client) {
            $client = Client::create([
                'name' => trim($data['name']),
                'email' => $email,
                'phone' => trim($data['phone']),
                'assigned_user_id' => $advisorId,
                'lead_source' => 'obligado_solidario',
                'initial_notes' => 'Obligado solidario de la renta #' . $rental->id . ' (inquilino: ' . ($rental->tenantClient?->name ?? '—') . ')'
                    . (! empty($data['relationship']) ? '. Relación: ' . trim($data['relationship']) : '') . '.',
            ]);
        }

        if (! $user) {
            $user = User::create([
                'name' => $client->name, 'email' => $email, 'phone' => $client->phone, 'role' => 'client',
                'password' => Str::random(40), // nunca se muestra ni se envía: entra con la invitación
            ]);
        }
        if (! $client->user_id) {
            $client->update(['user_id' => $user->id]);
        }

        $rental->update(['obligado_client_id' => $client->id, 'obligado_required' => $rental->obligado_required, 'obligado_invited_at' => now()]);
        $this->sendInvitation($rental->fresh(['tenantClient', 'obligado']), $user);
        $this->notifyAdvisor($rental, $client, $by);

        return $client;
    }

    /** Reenvía la invitación (enlace de activación de 7 días). */
    public function resendInvitation(RentalProcess $rental): bool
    {
        $rental->loadMissing(['tenantClient', 'obligado']);
        $user = $rental->obligado?->user_id ? User::find($rental->obligado->user_id) : null;
        if (! $user) {
            return false;
        }
        $rental->update(['obligado_invited_at' => now()]);

        return $this->sendInvitation($rental, $user);
    }

    private function sendInvitation(RentalProcess $rental, User $user): bool
    {
        $portal = app(ClientPortalService::class);
        $url = rtrim(config('portal.url', 'https://miportal.homedelvalle.mx'), '/') . '/activar/' . $portal->generateInvitationToken($user);
        $tenant = $rental->tenantClient?->name ?? 'una persona';
        $address = $rental->property?->address;

        try {
            return app(EmailService::class)->send(
                $user->email,
                'Te registraron como obligado solidario — completa tu información',
                '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#0f172a;line-height:1.55;max-width:560px;">'
                . '<p>Hola ' . e(explode(' ', trim($user->name))[0]) . ',</p>'
                . '<p><strong>' . e($tenant) . '</strong> te registró como <strong>obligado solidario</strong> de su renta' . ($address ? ' en <strong>' . e($address) . '</strong>' : '') . ' con Home del Valle. '
                . 'Es la persona que responde junto con el inquilino y por eso necesitamos tus datos y unos documentos.</p>'
                . '<p><strong>Qué necesitamos de ti</strong> (5–10 minutos, desde tu celular):</p>'
                . '<ul style="padding-left:20px;"><li>Tus datos personales, domicilio y trabajo</li><li>Tu identificación (INE por ambos lados o pasaporte)</li><li>Un comprobante de domicilio reciente</li><li>Tus comprobantes de ingresos de los últimos 3 meses (nómina, estados de cuenta o CFDI)</li></ul>'
                . '<p style="margin:24px 0;"><a href="' . e($url) . '" style="background:#1D4ED8;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;display:inline-block;">Activar mi cuenta y empezar</a></p>'
                . '<p style="font-size:13px;color:#475569;">Tu información es confidencial y solo la ve tu asesor de Home del Valle; el inquilino no tiene acceso a tus datos ni a tus documentos. El enlace vence en 7 días.</p>'
                . '<p>Saludos,<br>Home del Valle Bienes Raíces</p></div>',
                $user->name,
            );
        } catch (\Throwable $e) {
            Log::warning('ObligadoSolidarioService: no se pudo enviar la invitación', ['rental_id' => $rental->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function notifyAdvisor(RentalProcess $rental, Client $os, string $by): void
    {
        $userId = $rental->broker_id ?? $rental->user_id;
        if (! $userId) {
            return;
        }
        Notification::create([
            'user_id' => $userId,
            'type' => 'obligado_registrado',
            'title' => 'Obligado solidario registrado',
            'body' => ($by === 'tenant' ? 'El inquilino registró' : 'Se registró') . " a {$os->name} como obligado solidario de la renta #{$rental->id}; ya recibió su invitación al Portal.",
            'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
        ]);
    }

    /** ¿Ya subió algún documento a esta renta (o capturó datos)? Protege contra cambiar a alguien que ya empezó. */
    public function hasStarted(RentalProcess $rental): bool
    {
        return $rental->obligado_client_id
            && Document::where('client_id', $rental->obligado_client_id)->where('rental_process_id', $rental->id)->exists();
    }

    /** Avance de los DATOS del obligado (mismos campos que el asistente del inquilino, sin hogar/referencias). */
    public function dataProgress(Client $c): int
    {
        $fields = array_merge(ExpedienteFields::PERSONAL, ExpedienteFields::IDENTIFICATION, ExpedienteFields::INCOME_OBLIGADO);
        $filled = collect($fields)->filter(fn($f) => ! empty($c->{$f}))->count();

        return (int) round($filled / count($fields) * 100);
    }

    /**
     * Estado para el inquilino y el asesor.
     *
     * @return array{registered:bool, client:?Client, name:?string, data_pct:int, docs:array, docs_missing:array, complete:bool, invited_at:mixed, started:bool}
     */
    public function status(RentalProcess $rental): array
    {
        $os = $rental->obligado_client_id ? ($rental->relationLoaded('obligado') ? $rental->obligado : $rental->obligado()->first()) : null;
        if (! $os) {
            return ['registered' => false, 'client' => null, 'name' => null, 'data_pct' => 0, 'docs' => ['aprobado' => 0, 'revision' => 0, 'corregir' => 0, 'falta' => 0], 'docs_missing' => [], 'complete' => false, 'invited_at' => null, 'started' => false];
        }

        $rows = TenantDocumentRows::build($rental, $os, null, true);
        $missing = RentalExpedienteStatus::missing($rental, $os->id);
        $pct = $this->dataProgress($os);

        return [
            'registered' => true, 'client' => $os, 'name' => $os->name, 'data_pct' => $pct,
            'docs' => $rows['counts'], 'docs_missing' => $missing,
            'complete' => $pct >= 100 && empty($missing),   // datos completos y documentos APROBADOS
            'invited_at' => $rental->obligado_invited_at, 'started' => $this->hasStarted($rental),
        ];
    }
}
