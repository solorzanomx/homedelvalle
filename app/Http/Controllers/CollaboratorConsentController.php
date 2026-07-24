<?php

namespace App\Http\Controllers;

use App\Mail\V4\Mailables\CollaboratorAuthorizedMail;
use App\Models\Collaborator;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CollaboratorConsentController extends Controller
{
    public function show(string $token)
    {
        $collaborator = Collaborator::where('consent_token', $token)->firstOrFail();

        if ($collaborator->consent_status !== 'pending') {
            return view('collaborator-response.already-responded', compact('collaborator'));
        }

        return view('collaborator-response.show', compact('collaborator'));
    }

    public function authorizeConsent(Request $request, string $token)
    {
        $collaborator = Collaborator::where('consent_token', $token)->firstOrFail();

        if ($collaborator->consent_status !== 'pending') {
            return view('collaborator-response.already-responded', compact('collaborator'));
        }

        $collaborator->update([
            'consent_status'     => 'authorized',
            'consent_snapshot'   => $collaborator->currentSnapshot(),
            'consent_at'         => now(),
            'consent_ip'         => $request->ip(),
            'consent_user_agent' => (string) $request->userAgent(),
        ]);

        if ($collaborator->email) {
            try {
                Mail::to($collaborator->email)->send(new CollaboratorAuthorizedMail($collaborator));
                $collaborator->update(['confirmation_email_sent_at' => now()]);
            } catch (\Exception $e) {
                Log::warning('CollaboratorAuthorizedMail failed: ' . $e->getMessage());
            }
        }

        $this->notifyAdmin($collaborator, "{$collaborator->name} autorizó su publicación en el sitio.");

        return view('collaborator-response.authorized', compact('collaborator'));
    }

    public function decline(Request $request, string $token)
    {
        $collaborator = Collaborator::where('consent_token', $token)->firstOrFail();

        if ($collaborator->consent_status !== 'pending') {
            return view('collaborator-response.already-responded', compact('collaborator'));
        }

        $request->validate(['decline_note' => 'nullable|string|max:500']);

        $collaborator->update([
            'consent_status'     => 'declined',
            'consent_at'         => now(),
            'consent_ip'         => $request->ip(),
            'consent_user_agent' => (string) $request->userAgent(),
            'decline_note'       => $request->decline_note,
        ]);

        $body = "{$collaborator->name} no autorizó su publicación.";
        if ($request->decline_note) {
            $body .= ' Nota: ' . $request->decline_note;
        }
        $this->notifyAdmin($collaborator, $body);

        return view('collaborator-response.declined', compact('collaborator'));
    }

    private function notifyAdmin(Collaborator $collaborator, string $body): void
    {
        $adminId = User::where('role', 'admin')->value('id');
        if (!$adminId) {
            return;
        }

        Notification::create([
            'user_id' => $adminId,
            'type'    => 'system',
            'title'   => 'Respuesta de colaborador',
            'body'    => $body,
            'data'    => ['url' => route('admin.collaborators.index'), 'collaborator_id' => $collaborator->id],
        ]);
    }
}
