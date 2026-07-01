<?php

namespace App\Services;

use App\Mail\V4\Data\CitaData;
use App\Mail\V4\Mailables\CitaMail;
use App\Models\Client;
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
 * (ClientController::storeInteraction) y el atajo "Agendar visita" de la
 * ficha de captación (CaptacionAdminController::scheduleVisit).
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
        $interaction = Interaction::create([
            'client_id'               => $client->id,
            'user_id'                 => $broker->id,
            'type'                    => 'visit',
            'description'             => $description ?? 'Visita agendada',
            'completed_at'            => null,
            'scheduled_at'            => $scheduledAt,
            'visit_token'             => Str::uuid()->toString(),
            'send_confirmation_email' => $sendConfirmationEmail,
            'property_id'             => $property?->id,
        ]);

        app(LeadScoringService::class)->processEvent($client->id, 'visit_scheduled', ['source' => 'interaction']);

        // Passive scoring for the property owner when a visit is scheduled
        if ($property && $property->owner && $property->owner->id !== $client->id) {
            app(LeadScoringService::class)->processEvent(
                $property->owner->id,
                'message_sent',
                ['source' => 'property_visit_scheduled', 'property_id' => $property->id]
            );
        }

        if ($sendConfirmationEmail && $client->email) {
            $this->sendConfirmationEmail($interaction, $asesorForEmail ?? $broker, $duracionMinutos);
        }

        return $interaction;
    }

    public function sendConfirmationEmail(Interaction $interaction, User $asesor, string $duracionMinutos = '30'): void
    {
        $client = $interaction->client;
        if (!$client?->email || !$interaction->scheduled_at) {
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

            Mail::to($client->email)->send(
                new CitaMail(
                    new CitaData(
                        email: $client->email,
                        nombre: $client->name,
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
