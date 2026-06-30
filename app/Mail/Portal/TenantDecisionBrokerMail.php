<?php

namespace App\Mail\Portal;

use App\Models\Client;
use App\Models\RentalProcess;
use App\Models\TenantInvestigation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantDecisionBrokerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly RentalProcess $rental,
        public readonly TenantInvestigation $investigation,
        public readonly Client $owner,
    ) {}

    public function envelope(): Envelope
    {
        $labels = [
            'approved'  => 'aprobó al candidato',
            'declined'  => 'declinó al candidato',
            'more_info' => 'solicita más información',
        ];
        $label = $labels[$this->investigation->owner_decision] ?? 'respondió sobre el candidato';
        $name  = $this->owner->name ?? 'El propietario';
        return new Envelope(subject: "{$name} {$label}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.portal.tenant-decision-broker');
    }
}
