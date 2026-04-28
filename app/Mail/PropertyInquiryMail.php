<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PropertyInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $propertyTitle,
        public string $email,
        public string $phone,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.from.address'),
            subject: "Confirmamos tu interés en: {$this->propertyTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.property-inquiry',
            with: [
                'name' => $this->name,
                'propertyTitle' => $this->propertyTitle,
                'email' => $this->email,
                'phone' => $this->phone,
            ],
        );
    }
}
