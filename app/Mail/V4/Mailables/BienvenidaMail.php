<?php

namespace App\Mail\V4\Mailables;

use App\Mail\V4\Data\BienvenidaData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly BienvenidaData $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Bienvenido al área de clientes'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.bienvenida',
            with: ['data' => $this->data]
        );
    }
}
