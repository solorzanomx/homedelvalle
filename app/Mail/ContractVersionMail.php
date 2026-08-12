<?php

namespace App\Mail;

use App\Models\ContractVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractVersionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ContractVersion $version,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "{$this->version->contract->title} — Versión {$this->version->version_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contracts.version-sent',
        );
    }

    public function attachments(): array
    {
        if (!$this->version->pdf_path) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->version->pdf_path)
                ->as(str_replace(' ', '_', $this->version->contract->title) . '-v' . $this->version->version_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
