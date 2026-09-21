<?php

namespace App\Mail\V4\Mailables;

use App\Models\Interaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Alerta al broker cuando la calificación de una visita es negativa
 * (visitor_reaction=disliked) o el precio se percibió alto
 * (price_perception=high) — la idea es que puedas hablar con el dueño de
 * inmediato para sensibilizarlo, en vez de enterarte hasta que revises el
 * timeline del cliente/lead a mano.
 */
class VisitFeedbackAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Interaction $interaction) {}

    public function envelope(): Envelope
    {
        $name = $this->interaction->contactName() ?? 'Un visitante';
        $addr = $this->interaction->property?->address ?? 'el inmueble';
        return new Envelope(subject: "⚠️ Calificación a revisar: {$name} — {$addr}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.v4.visit-feedback-alert');
    }
}
