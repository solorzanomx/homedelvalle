<?php

namespace App\Listeners;

use App\Models\Broker;
use App\Models\Client;
use App\Models\FormSubmission;
use App\Models\Message;
use Illuminate\Mail\Events\MessageSent;

/**
 * Se dispara en CADA correo enviado por Laravel Mail (Mailables del sistema
 * V4 — acuse, citas, bienvenida, etc. — y cualquier Mail::html/Mail::send),
 * sin necesidad de tocar cada clase una por una.
 *
 * Si el remitente ya creó su propia fila en Message (ej. CustomEmailTemplate)
 * la completa con el ID real de Resend vía el header X-Message-Row-Id. Si
 * no, crea una fila nueva con lo que se puede inferir (destinatario,
 * asunto, y el Client/Broker/Lead que tenga ese correo, si existe).
 */
class RecordSentMessage
{
    public function handle(MessageSent $event): void
    {
        $symfonyMessage = $event->sent->getSymfonySentMessage()->getOriginalMessage();
        $headers = $symfonyMessage->getHeaders();

        $resendId = optional($headers->get('X-Resend-Email-ID'))->getBodyAsString() ?: null;
        $rowIdHeader = $headers->get('X-Message-Row-Id');

        if ($rowIdHeader) {
            $message = Message::find((int) $rowIdHeader->getBodyAsString());
            if ($message) {
                $message->update(['external_id' => $resendId]);
                if ($message->status === 'queued') {
                    $message->markSent();
                }
            }
            return;
        }

        $to = collect($symfonyMessage->getTo())->map(fn ($addr) => $addr->getAddress())->first();
        if (!$to) {
            return;
        }

        if ($resendId && Message::where('external_id', $resendId)->exists()) {
            return;
        }

        [$trackableType, $trackableId, $clientId] = $this->resolveTrackable($to);

        Message::create([
            'client_id' => $clientId,
            'trackable_type' => $trackableType,
            'trackable_id' => $trackableId,
            'channel' => 'email',
            'subject' => $symfonyMessage->getSubject(),
            'body' => $symfonyMessage->getHtmlBody() ?: ($symfonyMessage->getTextBody() ?: ''),
            'status' => 'sent',
            'sent_at' => now(),
            'external_id' => $resendId,
            'metadata' => ['to' => $to, 'auto_tracked' => true],
        ]);
    }

    /** @return array{0: ?string, 1: ?int, 2: ?int} [trackable_type, trackable_id, client_id] */
    private function resolveTrackable(string $email): array
    {
        if ($client = Client::where('email', $email)->first()) {
            return [Client::class, $client->id, $client->id];
        }
        if ($broker = Broker::where('email', $email)->first()) {
            return [Broker::class, $broker->id, null];
        }
        if ($lead = FormSubmission::where('email', $email)->latest()->first()) {
            return [FormSubmission::class, $lead->id, null];
        }

        return [null, null, null];
    }
}
