<?php

namespace App\Notifications;

use App\Models\Interaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VisitResponseNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Interaction $interaction,
        private readonly string $type // 'confirmed' or 'reschedule'
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toDatabase($notifiable): array
    {
        $client = $this->interaction->client;
        $name = $client?->name ?? 'El cliente';

        if ($this->type === 'confirmed') {
            return [
                'title' => 'Visita confirmada',
                'body'  => "{$name} confirmó su asistencia para hoy.",
                'url'   => $client ? route('clients.show', $client) : null,
                'icon'  => 'check',
            ];
        }

        return [
            'title' => 'Solicitud de reagendamiento',
            'body'  => "{$name} quiere reagendar la visita. Mensaje: " . ($this->interaction->reschedule_message ?? ''),
            'url'   => $client ? route('clients.show', $client) : null,
            'icon'  => 'calendar',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $client = $this->interaction->client;
        $name = $client?->name ?? 'El cliente';

        if ($this->type === 'confirmed') {
            return (new MailMessage)
                ->subject("Visita confirmada: {$name}")
                ->greeting('Visita confirmada')
                ->line("{$name} confirmó que asistirá a la visita de hoy.")
                ->line('Hora: ' . ($this->interaction->scheduled_at?->format('H:i') ?? '—'))
                ->action('Ver cliente', $client ? route('clients.show', $client) : url('/'));
        }

        return (new MailMessage)
            ->subject("{$name} quiere reagendar su visita")
            ->greeting('Solicitud de reagendamiento')
            ->line("{$name} no puede asistir y solicita reagendar.")
            ->line('Mensaje: ' . ($this->interaction->reschedule_message ?? 'Sin mensaje'))
            ->action('Ver cliente', $client ? route('clients.show', $client) : url('/'));
    }
}
