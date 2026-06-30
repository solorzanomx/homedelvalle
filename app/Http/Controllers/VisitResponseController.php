<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
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

        // Notify the broker (user who created the interaction)
        if ($interaction->user) {
            $interaction->user->notify(new VisitResponseNotification($interaction, 'confirmed'));
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

        if ($interaction->user) {
            $interaction->user->notify(new VisitResponseNotification($interaction, 'reschedule'));
        }

        return view('visit-response.reschedule-sent', compact('interaction'));
    }
}
