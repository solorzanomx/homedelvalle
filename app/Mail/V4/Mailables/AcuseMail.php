<?php

namespace App\Mail\V4\Mailables;

use App\Mail\V4\Data\AcuseData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AcuseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly AcuseData $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Recibimos tu mensaje'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.acuse',
            with: ['data' => $this->data]
        );
    }
}
