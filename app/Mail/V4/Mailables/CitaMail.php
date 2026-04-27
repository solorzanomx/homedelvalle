<?php

namespace App\Mail\V4\Mailables;

use App\Mail\V4\Data\CitaData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CitaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly CitaData $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Tu visita está agendada para {$this->data->dia_semana}, {$this->data->dia} de {$this->data->mes}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.cita',
            with: ['data' => $this->data]
        );
    }
}
