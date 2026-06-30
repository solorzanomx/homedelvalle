<?php

namespace App\Mail\Portal;

use App\Models\Interaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class VisitConfirmedOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Interaction $interaction,
    ) {}

    public function envelope(): Envelope
    {
        $date = $this->interaction->scheduled_at?->locale('es')->isoFormat('dddd D [de] MMMM') ?? 'próximamente';

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Visita confirmada para el {$date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.portal.visit-confirmed-owner',
        );
    }
}
