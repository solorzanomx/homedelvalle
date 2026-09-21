<?php

namespace App\Services;

use App\Mail\V4\Data\CitaData;
use App\Mail\V4\Mailables\CitaMail;
use App\Models\Client;
use App\Models\FormSubmission;
use App\Models\Interaction;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Agenda una visita (Interaction type=visit con visit_token) y envía la
 * confirmación por email — lógica compartida entre el perfil de cliente
 * (ClientController::storeInteraction), el atajo "Agendar visita" de la
 * ficha de captación (CaptacionAdminController::scheduleVisit), y desde
 * 2026-09-21 tambien la ficha de un lead sin convertir todavia
 * (FormSubmissionController::scheduleVisit) — ver createVisitForLead().
 * Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md sección 2.3.
 */
class VisitSchedulingService
{
    public function createVisit(
        Client $client,
        ?Property $property,
        User $broker,
        Carbon $scheduledAt,
        bool $sendConfirmationEmail = true,
        ?string $description = null,
        ?User $asesorForEmail = null,
        string $duracionMinutos = '30',
    ): Interaction {
        $interaction = $this->create(
            client: $client,
            lead: null,
            property: $property,
            broker: $broker,
            scheduledAt: $scheduledAt,
            sendConfirmationEmail: $sendConfirmationEmail,
            description: $description,
            asesorForEmail: $asesorForEmail,
            duracionMinutos: $duracionMinutos,
        );

        app(LeadScoringService::class)->processEvent($client->id, 'visit_scheduled', ['source' => 'interaction']);

        // Passive scoring for the property owner when a visit is scheduled
        if ($property && $property->owner && $property->owner->id !== $client->id) {
            app(LeadScoringService::class)->processEvent(
                $property->owner->id,
                'message_sent',
                ['source' => 'property_visit_scheduled', 'property_id' => $property->id]
            );
        }

        return $interaction;
    }

    /**
     * Igual que createVisit(), pero para un lead que todavia no se convierte
     * a Client — no dispara lead scoring (esa maquinaria vive en client_id;
     * arranca a puntuar en cuanto el broker decida convertirlo).
     */
    public function createVisitForLead(
        FormSubmission $lead,
        ?Property $property,
        User $broker,
        Carbon $scheduledAt,
        bool $sendConfirmationEmail = true,
        ?string $description = null,
        ?User $asesorForEmail = null,
        string $duracionMinutos = '30',
    ): Interaction {
        return $this->create(
            client: null,
            lead: $lead,
            property: $property,
            broker: $broker,
            scheduledAt: $scheduledAt,
            sendConfirmationEmail: $sendConfirmationEmail,
            description: $description,
            asesorForEmail: $asesorForEmail,
            duracionMinutos: $duracionMinutos,
        );
    }

    private function create(
        ?Client $client,
        ?FormSubmission $lead,
        ?Property $property,
        User $broker,
        Carbon $scheduledAt,
        bool $sendConfirmationEmail,
        ?string $description,
        ?User $asesorForEmail,
        string $duracionMinutos,
    ): Interaction {
        $interaction = Interaction::create([
            'client_id'               => $client?->id,
            'form_submission_id'      => $lead?->id,
            'user_id'                 => $broker->id,
            'type'                    => 'visit',
            'description'             => $description ?? 'Visita agendada',
            'completed_at'            => null,
            'scheduled_at'            => $scheduledAt,
            'visit_token'             => Str::uuid()->toString(),
            'send_confirmation_email' => $sendConfirmationEmail,
            'property_id'             => $property?->id,
        ]);

        if ($sendConfirmationEmail && $interaction->contactEmail()) {
            $this->sendConfirmationEmail($interaction, $asesorForEmail ?? $broker, $duracionMinutos);
        }

        return $interaction;
    }

    public function sendConfirmationEmail(Interaction $interaction, User $asesor, string $duracionMinutos = '30'): void
    {
        $email = $interaction->contactEmail();
        if (!$email || !$interaction->scheduled_at) {
            return;
        }

        try {
            $scheduled = $interaction->scheduled_at;
            $prop      = $interaction->property;

            $addressParts = array_filter([
                $prop?->address ?? '',
                $prop?->colony ?? '',
                $prop?->city ?? 'CDMX',
            ]);
            $address = urlencode(implode(', ', $addressParts));
            $mapsUrl = $address ? "https://www.google.com/maps/search/?api=1&query={$address}" : '';

            Mail::to($email)->send(
                new CitaMail(
                    new CitaData(
                        email: $email,
                        nombre: $interaction->contactName() ?? 'Cliente',
                        dia_semana: $scheduled->locale('es')->dayName,
                        dia: (string) $scheduled->day,
                        mes: $scheduled->locale('es')->monthName,
                        anio: (string) $scheduled->year,
                        hora: $scheduled->format('g:i A'),
                        duracion: $duracionMinutos,
                        direccion: $prop?->address ?? 'A coordinar',
                        colonia: $prop?->colony ?? '',
                        asesor: $asesor->name ?? 'Tu asesor',
                        visit_token: $interaction->visit_token,
                        maps_url: $mapsUrl,
                        asesor_email: $asesor->email ?? '',
                        asesor_phone: $asesor->phone ?? '',
                    )
                )
            );
        } catch (\Exception $e) {
            Log::warning('VisitSchedulingService: confirmation email failed: ' . $e->getMessage());
        }
    }
}
