<?php

namespace App\Mail\Portal;

use App\Models\Interaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class VisitRescheduledOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Interaction $interaction,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Un visitante solicita reagendar su visita',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.portal.visit-rescheduled-owner',
        );
    }
}
