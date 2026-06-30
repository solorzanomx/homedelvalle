<?php

namespace App\Mail\Portal;

use App\Models\RentalProcess;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantProposedOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly RentalProcess $rental) {}

    public function envelope(): Envelope
    {
        $addr = $this->rental->property?->address ?? 'tu inmueble';
        return new Envelope(subject: "Tenemos un candidato para {$addr}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.portal.tenant-proposed');
    }
}
