<?php

namespace App\Notifications;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FormSubmission $submission) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nuevo lead [{$this->submission->lead_tag}] · {$this->submission->full_name}")
            ->line("**Tipo de formulario:** {$this->submission->getFormTypeLabel()}")
            ->line("**Nombre:** {$this->submission->full_name}")
            ->line("**Email:** {$this->submission->email}")
            ->line("**Teléfono:** {$this->submission->phone}")
            ->line("**Origen:** {$this->submission->source_page}")
            ->line("**Tag:** {$this->submission->lead_tag}")
            ->line("**IP:** {$this->submission->ip}")
            ->line("**UTM Source:** {$this->submission->utm_source ?? 'N/A'}")
            ->line("**UTM Medium:** {$this->submission->utm_medium ?? 'N/A'}")
            ->line("**UTM Campaign:** {$this->submission->utm_campaign ?? 'N/A'}")
            ->line("---")
            ->line("**Payload:**")
            ->line(json_encode($this->submission->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
            ->action('Ver en admin', url('/admin/form-submissions/' . $this->submission->id));
    }
}
