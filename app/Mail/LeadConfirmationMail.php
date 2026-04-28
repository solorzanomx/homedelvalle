<?php

namespace App\Mail;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormSubmission $submission,
        public string $responseTime = '24 horas'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibimos tu solicitud · Home del Valle',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lead-confirmation',
            with: [
                'submission' => $this->submission,
                'responseTime' => $this->responseTime,
                'formTypeLabel' => $this->submission->getFormTypeLabel(),
            ],
        );
    }
}
