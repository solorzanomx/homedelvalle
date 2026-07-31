<?php

namespace App\Listeners;

use App\Models\Broker;
use App\Models\Message;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailFailed;
use Resend\Laravel\Events\EmailOpened;

/**
 * Traduce los webhooks de Resend (llegan como eventos de Laravel gracias al
 * paquete resend/resend-laravel) al estado real de nuestra tabla Message.
 * external_id guarda el ID que Resend regresó al enviar (email_id, para
 * envíos vía Mail::mailer('resend')) — se busca también por message_id por
 * si algún envío solo dejó ese identificador.
 */
class HandleResendWebhook
{
    public function handleOpened(EmailOpened $event): void
    {
        $message = $this->findMessage($event->payload);
        if (!$message) {
            return;
        }

        $isFirstOpen = is_null($message->opened_at);
        $message->markOpened();

        // Denormalizado en Broker para el badge "Abrió el correo" en la
        // lista de Brokers Externos, sin join en esa vista.
        if ($isFirstOpen && $message->trackable_type === Broker::class && $message->trackable_id) {
            Broker::whereKey($message->trackable_id)->update(['email_opened_at' => now()]);
        }
    }

    public function handleClicked(EmailClicked $event): void
    {
        $this->findMessage($event->payload)?->markClicked();
    }

    public function handleDelivered(EmailDelivered $event): void
    {
        $this->findMessage($event->payload)?->markDelivered();
    }

    public function handleBounced(EmailBounced $event): void
    {
        $this->findMessage($event->payload)?->markBounced();
    }

    public function handleComplained(EmailComplained $event): void
    {
        $this->findMessage($event->payload)?->markComplained();
    }

    public function handleFailed(EmailFailed $event): void
    {
        $this->findMessage($event->payload)?->markFailed();
    }

    private function findMessage(array $payload): ?Message
    {
        $data = $payload['data'] ?? [];
        $emailId = $data['email_id'] ?? null;
        $messageId = $data['message_id'] ?? null;

        if (!$emailId && !$messageId) {
            return null;
        }

        return Message::where(function ($q) use ($emailId, $messageId) {
            if ($emailId) {
                $q->orWhere('external_id', $emailId);
            }
            if ($messageId) {
                $q->orWhere('external_id', $messageId);
            }
        })->first();
    }
}
