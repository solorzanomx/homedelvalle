<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Deal;
use App\Models\FormSubmission;
use App\Models\Interaction;
use App\Models\Property;

/**
 * Convierte un FormSubmission (lead) en un Client — un solo lugar para esta lógica, usado tanto
 * por FormSubmissionsTable (Livewire, botón rápido de la lista) como por
 * Admin\FormSubmissionController::convertToClient() (desde la ficha del lead). Antes cada uno
 * tenía su propia copia de "crear o encontrar el Client", y ninguna "adoptaba" el historial de
 * un contacto que llegó por MÁS de un formulario.
 *
 * Caso real que lo motivó (2026-10-01): Yarlin llegó dos veces para el mismo depa — un alta
 * manual el 25/sep y el correo real de Inmuebles24 el 28/sep. Se convirtió el segundo, pero su
 * visita YA CONFIRMADA (con feedback real, 5/5 al asesor) colgaba del PRIMER form_submission_id
 * — quedó huérfana, sin client_id, invisible en la ficha del cliente nuevo. Y el dato de qué
 * depa le interesaba (`payload.propiedad_local_id`, lo pone Inmuebles24LeadImporter) nunca se
 * leía — había que ir a buscar el depa a mano para armar el trato de renta.
 */
class LeadConversionService
{
    /**
     * @return array{client: Client, was_existing: bool, reparented_leads: int,
     *               reparented_interactions: int, property_id: ?int, owner_client_id: ?int}
     */
    public function convert(FormSubmission $lead): array
    {
        [$client, $wasExisting] = $this->resolveOrCreateClient($lead);

        $siblingIds = $this->adoptSiblingLeads($lead, $client);
        $reparentedInteractions = $this->reparentInteractions($siblingIds, $client);

        $propertyId = $this->findInterestedPropertyId($siblingIds);
        $ownerClientId = $propertyId ? Property::find($propertyId)?->client_id : null;

        // Hallazgo 2026-10-02 (mismo caso de Yarlin): detectar la propiedad no bastaba — su
        // pestaña "Propiedades" seguía en 0 porque nada creaba el Deal que esa pestaña lee
        // (ClientController::show(), $dealProperties). firstOrCreate: si ya existe un trato
        // para este par cliente+propiedad (quizás ya avanzado de etapa), no se toca.
        if ($propertyId) {
            Deal::firstOrCreate(
                ['client_id' => $client->id, 'property_id' => $propertyId],
                ['stage' => 'lead']
            );
        }

        return [
            'client'                   => $client,
            'was_existing'             => $wasExisting,
            'reparented_leads'         => count($siblingIds) - 1, // sin contar al propio $lead
            'reparented_interactions'  => $reparentedInteractions,
            'property_id'              => $propertyId,
            'owner_client_id'          => $ownerClientId,
        ];
    }

    /** @return array{0: Client, 1: bool} */
    private function resolveOrCreateClient(FormSubmission $lead): array
    {
        if ($lead->client_id) {
            return [Client::findOrFail($lead->client_id), true];
        }

        $existing = $lead->email ? Client::where('email', $lead->email)->first() : null;
        if ($existing) {
            $lead->update(['client_id' => $existing->id]);
            return [$existing, true];
        }

        $client = Client::create([
            'name'             => $lead->full_name,
            'email'            => $lead->email,
            'phone'            => $lead->phone,
            'whatsapp'         => $lead->phone,
            'client_type'      => Client::deriveClientType($lead->interest_types ?? []) ?? $lead->client_type,
            'lead_temperature' => $lead->lead_temperature ?? 'warm',
            'budget_min'       => $lead->budget_min,
            'budget_max'       => $lead->budget_max,
            'property_type'    => $lead->property_type,
            'interest_types'   => $lead->interest_types,
            'utm_source'       => $lead->utm_source,
            'utm_medium'       => $lead->utm_medium,
            'utm_campaign'     => $lead->utm_campaign,
            'lead_source'      => 'form_' . $lead->form_type,
            'initial_notes'    => $lead->payload['mensaje'] ?? null,
        ]);
        $lead->update(['client_id' => $client->id]);

        return [$client, false];
    }

    /**
     * Cualquier otro FormSubmission con el mismo correo o teléfono que TODAVÍA no tenga cliente
     * se "adopta" — se le pone el mismo client_id. Nunca toca uno que ya pertenece a otro cliente.
     *
     * @return array<int> IDs de todos los leads del contacto (el convertido + los adoptados)
     */
    private function adoptSiblingLeads(FormSubmission $lead, Client $client): array
    {
        if (! $lead->email && ! $lead->phone) {
            return [$lead->id];
        }

        $siblings = FormSubmission::whereNull('client_id')
            ->where('id', '!=', $lead->id)
            ->where(function ($q) use ($lead) {
                if ($lead->email) {
                    $q->orWhere('email', $lead->email);
                }
                if ($lead->phone) {
                    $q->orWhere('phone', $lead->phone);
                }
            })
            ->get();

        foreach ($siblings as $sibling) {
            $sibling->update(['client_id' => $client->id]);
        }

        return [$lead->id, ...$siblings->pluck('id')->all()];
    }

    /** @param array<int> $formSubmissionIds */
    private function reparentInteractions(array $formSubmissionIds, Client $client): int
    {
        return Interaction::whereIn('form_submission_id', $formSubmissionIds)
            ->whereNull('client_id')
            ->update(['client_id' => $client->id]);
    }

    /**
     * Busca en los payloads de todos los leads del contacto un `propiedad_local_id` (lo pone
     * Inmuebles24LeadImporter cuando el código del aviso coincide con una Property real) — el
     * más reciente que tenga ese dato gana.
     *
     * @param array<int> $formSubmissionIds
     */
    private function findInterestedPropertyId(array $formSubmissionIds): ?int
    {
        $leads = FormSubmission::whereIn('id', $formSubmissionIds)
            ->whereNotNull('payload')
            ->latest()
            ->get(['payload']);

        foreach ($leads as $lead) {
            $propertyId = $lead->payload['propiedad_local_id'] ?? null;
            if ($propertyId) {
                return (int) $propertyId;
            }
        }

        return null;
    }
}
