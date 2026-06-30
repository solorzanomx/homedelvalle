<?php

namespace App\Mail\V4\Mailables;

use App\Models\Interaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitaConfirmadaNotificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Interaction $interaction) {}

    public function envelope(): Envelope
    {
        $name = $this->interaction->client?->name ?? 'El cliente';
        return new Envelope(subject: "✓ Visita confirmada: {$name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.v4.visita-confirmada-notificacion');
    }
}
