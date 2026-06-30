<?php

namespace App\Mail\V4\Mailables;

use App\Models\Client;
use App\Models\Interaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VisitFeedbackRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Interaction $interaction,
        public readonly Client $client,
        public readonly string $propertyAddress = '',
    ) {}

    public function envelope(): Envelope
    {
        $addr = $this->propertyAddress ?: 'el inmueble';
        return new Envelope(subject: "¿Qué te pareció {$addr}?");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.v4.visit-feedback-request');
    }
}
