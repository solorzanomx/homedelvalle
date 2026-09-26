<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\Notification;
use App\Models\RentalProcess;
use App\Support\ExpedienteFields;
use App\Support\RentalExpedienteStatus;
use App\Support\TenantDocumentRows;
use App\Support\TenantRoadmap;

/**
 * Obligado solidario (2026-09-28): cuando el inquilino NO tiene aval en CDMX (garantía = póliza) se le pide una persona
 * que responde con él y aporta LOS MISMOS datos y documentos: datos personales, identificación y domicilio,
 * trabajo/ingresos y sus 3 documentos (INE o pasaporte, domicilio, ingresos de los últimos 3 meses).
 *
 * QUÉ SE LE PIDE: el MISMO cuestionario del inquilino (datos personales, identificación y domicilio, 3 referencias
 * personales, datos del trabajo, antiguo arrendador) + sus 3 documentos; sin "información del hogar".
 *
 * QUIÉN LO LLENA: **el INQUILINO, desde su propio Portal** (decisión de Alejandro). El obligado NO tiene cuenta ni
 * Portal: es un Client "sin usuario" cuyos datos y documentos captura el inquilino (`?para=obligado`). El asesor lo ve
 * en el CRM como una persona más del trato y puede exentarlo por trato (`obligado_required = false`).
 */
class ObligadoSolidarioService
{
    /** ¿Este trato exige obligado solidario? Póliza (sin aval) y no exentado por el asesor. */
    public function isRequired(RentalProcess $rental): bool
    {
        return TenantRoadmap::route($rental) === TenantRoadmap::ROUTE_POLIZA && $rental->obligado_required !== false;
    }

    /**
     * Registra al obligado solidario (nombre, celular y, si hay, correo).
     *  - Inquilino: si ya hay uno, se EDITAN sus datos de contacto (sigue siendo la misma persona).
     *  - Asesor: siempre crea a una persona NUEVA y la vincula (así se cambia de persona sin mezclar datos ni documentos).
     *
     * @param  array{name:string,phone:string,email?:?string,relationship?:?string}  $data
     */
    public function register(RentalProcess $rental, array $data, string $by = 'tenant'): Client
    {
        $rental->loadMissing(['tenantClient', 'ownerClient', 'obligado']);
        $email = ! empty($data['email']) ? mb_strtolower(trim($data['email'])) : null;

        // El correo del obligado es OPCIONAL y no se usa para entrar a ningún lado. Si ya existe en el sistema
        // (clients.email es único) o es de otra parte del trato, se omite: nunca se toca el registro de otra persona.
        if ($email && (Client::where('email', $email)->exists()
            || in_array($email, array_filter([mb_strtolower((string) $rental->tenantClient?->email), mb_strtolower((string) $rental->ownerClient?->email)]), true))) {
            $email = ($rental->obligado && mb_strtolower((string) $rental->obligado->email) === $email) ? $email : null;
        }

        $fields = ['name' => trim($data['name']), 'phone' => trim($data['phone']), 'email' => $email];

        if ($by === 'tenant' && $rental->obligado) {
            $rental->obligado->update($fields);
            $client = $rental->obligado;
        } else {
            $client = Client::create($fields + [
                'assigned_user_id' => $rental->broker_id ?? $rental->user_id ?? $rental->tenantClient?->assigned_user_id,
                'lead_source' => 'obligado_solidario',
                'initial_notes' => 'Obligado solidario de la renta #' . $rental->id . ' (inquilino: ' . ($rental->tenantClient?->name ?? '—') . ')'
                    . (! empty($data['relationship']) ? '. Relación: ' . trim($data['relationship']) : '') . '. Sus datos y documentos los captura el inquilino en su Portal.',
            ]);
            $rental->update(['obligado_client_id' => $client->id]);
            $this->notifyAdvisor($rental, $client, $by);
        }

        return $client;
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
            'body' => ($by === 'tenant' ? 'El inquilino registró' : 'Se registró') . " a {$os->name} como obligado solidario de la renta #{$rental->id}. El inquilino captura sus datos y documentos en su Portal.",
            'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
        ]);
    }

    /** ¿Ya hay documentos de esta persona en el trato? */
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
        // Las 3 referencias personales también cuentan (mismo cuestionario que el inquilino).
        $refs = min($c->references()->count(), ExpedienteFields::REFERENCES_REQUIRED);

        return (int) round(($filled + $refs) / (count($fields) + ExpedienteFields::REFERENCES_REQUIRED) * 100);
    }

    /**
     * Estado para el inquilino y el asesor.
     *
     * @return array{registered:bool, client:?Client, name:?string, data_pct:int, docs:array, docs_missing:array, complete:bool, started:bool}
     */
    public function status(RentalProcess $rental): array
    {
        $os = $rental->obligado_client_id ? ($rental->relationLoaded('obligado') ? $rental->obligado : $rental->obligado()->first()) : null;
        if (! $os) {
            return ['registered' => false, 'client' => null, 'name' => null, 'data_pct' => 0, 'docs' => ['aprobado' => 0, 'revision' => 0, 'corregir' => 0, 'falta' => 0], 'docs_missing' => [], 'complete' => false, 'started' => false];
        }

        $rows = TenantDocumentRows::build($rental, $os, null, true);
        $missing = RentalExpedienteStatus::missing($rental, $os->id);
        $pct = $this->dataProgress($os);

        return [
            'registered' => true, 'client' => $os, 'name' => $os->name, 'data_pct' => $pct,
            'docs' => $rows['counts'], 'docs_missing' => $missing,
            'complete' => $pct >= 100 && empty($missing),   // datos completos y documentos APROBADOS
            'started' => $this->hasStarted($rental),
        ];
    }

    /**
     * ¿Puede este cliente del Portal capturar datos/documentos a nombre de ESE obligado? Solo el INQUILINO de la renta
     * a la que pertenece el obligado, mientras el trato lo exija. Es la única puerta: úsala en TODO endpoint `para=obligado`.
     */
    public function tenantMayActFor(?Client $tenant, ?int $obligadoClientId): ?RentalProcess
    {
        if (! $tenant || ! $obligadoClientId) {
            return null;
        }
        $rental = app(ClientPortalService::class)->activeTenantRental($tenant);

        return ($rental && (int) $rental->obligado_client_id === $obligadoClientId && $this->isRequired($rental)) ? $rental : null;
    }
}
