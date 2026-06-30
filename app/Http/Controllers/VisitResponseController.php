<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Notifications\VisitResponseNotification;

class VisitResponseController extends Controller
{
    public function confirm(string $token)
    {
        $interaction = Interaction::where('visit_token', $token)->firstOrFail();

        if ($interaction->confirmed_at) {
            return view('visit-response.already-confirmed');
        }

        $interaction->update(['confirmed_at' => now()]);

        // Fire visit_completed scoring for the interested client
        if ($interaction->client_id) {
            app(\App\Services\LeadScoringService::class)->processEvent(
                $interaction->client_id,
                'visit_completed',
                ['source' => 'visit_token_confirmed', 'interaction_id' => $interaction->id]
            );
        }

        // Notify the broker via custom notifications table + mail
        if ($interaction->user_id) {
            $client = $interaction->client;
            $name   = $client?->name ?? 'El cliente';

            Notification::create([
                'user_id' => $interaction->user_id,
                'type'    => 'system',
                'title'   => 'Visita confirmada',
                'body'    => "{$name} confirmó su asistencia para hoy a las " . ($interaction->scheduled_at?->format('H:i') ?? '—') . '.',
                'data'    => ['url' => $client ? route('clients.show', $client) : null, 'interaction_id' => $interaction->id],
            ]);

            try {
                $interaction->user->notify(new VisitResponseNotification($interaction, 'confirmed'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('VisitResponse mail failed: ' . $e->getMessage());
            }
        }

        // Notify the property owner via portal prefs (email only if they opted in)
        if ($interaction->property?->owner?->portalUser) {
            $ownerUser = $interaction->property->owner->portalUser;
            $prefs = \App\Models\PortalNotificationPreference::forUser($ownerUser->id);
            if ($prefs->notify_visit_confirmed) {
                try {
                    \Illuminate\Support\Facades\Mail::to($ownerUser->email)->send(
                        new \App\Mail\Portal\VisitConfirmedOwnerMail($interaction)
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('VisitConfirmedOwnerMail failed: ' . $e->getMessage());
                }
            }
        }

        return view('visit-response.confirmed', compact('interaction'));
    }

    public function reschedule(string $token)
    {
        $interaction = Interaction::where('visit_token', $token)->firstOrFail();
        return view('visit-response.reschedule', compact('interaction'));
    }

    public function rescheduleSubmit(Request $request, string $token)
    {
        $interaction = Interaction::where('visit_token', $token)->firstOrFail();

        $request->validate(['mensaje' => 'required|string|max:500']);

        $interaction->update([
            'reschedule_requested_at' => now(),
            'reschedule_message'      => $request->mensaje,
        ]);

        // Scoring: reagendar es señal de interés activo del cliente
        if ($interaction->client_id) {
            app(\App\Services\LeadScoringService::class)->processEvent(
                $interaction->client_id,
                'message_sent',
                ['source' => 'visit_reschedule_request', 'interaction_id' => $interaction->id]
            );
        }

        // Notify the broker via custom notifications table + mail
        if ($interaction->user_id) {
            $client = $interaction->client;
            $name   = $client?->name ?? 'El cliente';

            Notification::create([
                'user_id' => $interaction->user_id,
                'type'    => 'system',
                'title'   => 'Solicitud de reagendamiento',
                'body'    => "{$name} quiere reagendar su visita. Mensaje: " . ($interaction->reschedule_message ?? ''),
                'data'    => ['url' => $client ? route('clients.show', $client) : null, 'interaction_id' => $interaction->id],
            ]);

            try {
                $interaction->user->notify(new VisitResponseNotification($interaction, 'reschedule'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('VisitResponse mail failed: ' . $e->getMessage());
            }
        }

        // Notify the property owner if they opted in for rescheduled notifications
        if ($interaction->property?->owner?->portalUser) {
            $ownerUser = $interaction->property->owner->portalUser;
            $prefs = \App\Models\PortalNotificationPreference::forUser($ownerUser->id);
            if ($prefs->notify_visit_rescheduled) {
                try {
                    \Illuminate\Support\Facades\Mail::to($ownerUser->email)->send(
                        new \App\Mail\Portal\VisitRescheduledOwnerMail($interaction)
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('VisitRescheduledOwnerMail failed: ' . $e->getMessage());
                }
            }
        }

        return view('visit-response.reschedule-sent', compact('interaction'));
    }

    public function submitFeedback(Request $request, string $token)
    {
        $interaction = Interaction::where('visit_token', $token)->firstOrFail();

        $request->validate([
            'visitor_reaction' => 'required|in:liked,neutral,disliked',
            'visitor_comment'  => 'nullable|string|max:300',
        ]);

        $interaction->update([
            'visitor_reaction'      => $request->visitor_reaction,
            'visitor_comment'       => $request->visitor_comment,
            'feedback_submitted_at' => now(),
        ]);

        return view('visit-response.feedback-sent', compact('interaction'));
    }
}
