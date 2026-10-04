<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\Notification;
use App\Models\RentalProcess;
use App\Support\ExpedienteFields;
use App\Support\RentalExpedienteStatus;
use App\Support\TenantDocumentRows;

/**
 * Co-arrendatario (2026-10-03): cuando el contrato de arrendamiento queda a nombre de DOS personas (ej. una pareja
 * que renta junta), ambas son inquilinas por igual — a diferencia del obligado solidario (un respaldo que NO renta,
 * solo responde si el inquilino titular no paga). El co-arrendatario sí debe pasar la misma investigación completa
 * (buró, solvencia, referencias) que el titular.
 *
 * QUÉ SE LE PIDE: el MISMO cuestionario completo del inquilino titular (datos personales, identificación y
 * domicilio, 3 referencias personales, trabajo/ingresos con su comprobante, antiguo arrendador); sin "información
 * del hogar" (es la misma vivienda que el titular) ni garantía propia (la garantía del trato es una sola, ya
 * capturada por el titular).
 *
 * QUIÉN LO LLENA: igual que el obligado solidario — **el INQUILINO TITULAR, desde su propio Portal** (mismo patrón,
 * decisión de Alejandro 2026-10-03). El co-arrendatario NO tiene cuenta ni Portal propio: es un Client "sin
 * usuario" cuyos datos y documentos captura el titular (`?para=co_tenant`). A diferencia del obligado, agregar un
 * co-arrendatario es SIEMPRE opcional y manual (no lo dispara ninguna ruta de garantía) — lo registra el asesor
 * (por teléfono) o el propio inquilino desde su Portal, solo cuando de verdad van a rentar entre varios.
 */
class CoTenantService
{
    /** ¿Esta renta ya tiene un co-arrendatario asignado? */
    public function isPresent(RentalProcess $rental): bool
    {
        return (bool) $rental->co_tenant_client_id;
    }

    /**
     * Registra al co-arrendatario (nombre, celular y, si hay, correo).
     *  - Inquilino: si ya hay uno, se EDITAN sus datos de contacto (sigue siendo la misma persona).
     *  - Asesor: siempre crea a una persona NUEVA y la vincula (así se cambia de persona sin mezclar datos ni documentos).
     *
     * @param  array{name:string,phone:string,email?:?string,relationship?:?string}  $data
     */
    public function register(RentalProcess $rental, array $data, string $by = 'tenant'): Client
    {
        $rental->loadMissing(['tenantClient', 'ownerClient', 'coTenant']);
        $email = ! empty($data['email']) ? mb_strtolower(trim($data['email'])) : null;

        // El correo del co-arrendatario es OPCIONAL. Si ya existe en el sistema (clients.email es único) o es de
        // otra parte del trato, se omite: nunca se toca el registro de otra persona.
        if ($email && (Client::where('email', $email)->exists()
            || in_array($email, array_filter([mb_strtolower((string) $rental->tenantClient?->email), mb_strtolower((string) $rental->ownerClient?->email)]), true))) {
            $email = ($rental->coTenant && mb_strtolower((string) $rental->coTenant->email) === $email) ? $email : null;
        }

        $fields = ['name' => trim($data['name']), 'phone' => trim($data['phone']), 'email' => $email];

        if ($by === 'tenant' && $rental->coTenant) {
            $rental->coTenant->update($fields);
            $client = $rental->coTenant;
        } else {
            $client = Client::create($fields + [
                'assigned_user_id' => $rental->broker_id ?? $rental->user_id ?? $rental->tenantClient?->assigned_user_id,
                'lead_source' => 'co_tenant',
                'initial_notes' => 'Co-arrendatario de la renta #' . $rental->id . ' (inquilino titular: ' . ($rental->tenantClient?->name ?? '—') . ')'
                    . (! empty($data['relationship']) ? '. Relación: ' . trim($data['relationship']) : '') . '. Sus datos y documentos los captura el inquilino titular en su Portal.',
            ]);
            $rental->update(['co_tenant_client_id' => $client->id]);
            $this->notifyAdvisor($rental, $client, $by);
        }

        return $client;
    }

    private function notifyAdvisor(RentalProcess $rental, Client $ct, string $by): void
    {
        $userId = $rental->broker_id ?? $rental->user_id;
        if (! $userId) {
            return;
        }
        Notification::create([
            'user_id' => $userId,
            'type' => 'co_tenant_registrado',
            'title' => 'Co-arrendatario registrado',
            'body' => ($by === 'tenant' ? 'El inquilino registró' : 'Se registró') . " a {$ct->name} como co-arrendatario de la renta #{$rental->id}. El inquilino titular captura sus datos y documentos en su Portal.",
            'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
        ]);
    }

    /** ¿Ya hay documentos de esta persona en el trato? */
    public function hasStarted(RentalProcess $rental): bool
    {
        return $rental->co_tenant_client_id
            && Document::where('client_id', $rental->co_tenant_client_id)->where('rental_process_id', $rental->id)->exists();
    }

    /** Avance de los DATOS del co-arrendatario (mismo cuestionario completo que el titular, con su comprobante de ingresos). */
    public function dataProgress(Client $c): int
    {
        $fields = array_merge(ExpedienteFields::PERSONAL, ExpedienteFields::IDENTIFICATION, ExpedienteFields::INCOME_TENANT);
        $filled = collect($fields)->filter(fn($f) => ! empty($c->{$f}))->count();
        $refs = min($c->references()->valid()->count(), ExpedienteFields::REFERENCES_REQUIRED);

        return (int) round(($filled + $refs) / (count($fields) + ExpedienteFields::REFERENCES_REQUIRED) * 100);
    }

    /**
     * Estado para el inquilino titular y el asesor.
     *
     * @return array{registered:bool, client:?Client, name:?string, data_pct:int, docs:array, docs_missing:array, complete:bool, started:bool}
     */
    public function status(RentalProcess $rental): array
    {
        $ct = $rental->co_tenant_client_id ? ($rental->relationLoaded('coTenant') ? $rental->coTenant : $rental->coTenant()->first()) : null;
        if (! $ct) {
            return ['registered' => false, 'client' => null, 'name' => null, 'data_pct' => 0, 'docs' => ['aprobado' => 0, 'revision' => 0, 'corregir' => 0, 'falta' => 0], 'docs_missing' => [], 'complete' => false, 'started' => false];
        }

        $rows = TenantDocumentRows::build($rental, $ct, null, true);
        $missing = RentalExpedienteStatus::missing($rental, $ct->id);
        $pct = $this->dataProgress($ct);

        return [
            'registered' => true, 'client' => $ct, 'name' => $ct->name, 'data_pct' => $pct,
            'docs' => $rows['counts'], 'docs_missing' => $missing,
            'complete' => $pct >= 100 && empty($missing),   // datos completos y documentos APROBADOS
            'started' => $this->hasStarted($rental),
        ];
    }

    /**
     * ¿Puede este cliente del Portal capturar datos/documentos a nombre de ESE co-arrendatario? Solo el INQUILINO
     * TITULAR de la renta a la que pertenece. Es la única puerta: úsala en TODO endpoint `para=co_tenant`.
     */
    public function tenantMayActFor(?Client $tenant, ?int $coTenantClientId): ?RentalProcess
    {
        if (! $tenant || ! $coTenantClientId) {
            return null;
        }
        $rental = app(ClientPortalService::class)->activeTenantRental($tenant);

        return ($rental && (int) $rental->co_tenant_client_id === $coTenantClientId) ? $rental : null;
    }
}
