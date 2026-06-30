<?php

namespace App\Mail\Portal;

use App\Models\Interaction;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WeeklyPropertySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $owner,
        public readonly Property $property,
        public readonly Collection $visits,
        public readonly Carbon $weekStart,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->visits->count();
        $label = $count === 1 ? 'visita' : 'visitas';

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Tu inmueble esta semana — {$count} {$label}",
        );
    }

    public function content(): Content
    {
        $confirmedVisits  = $this->visits->whereNotNull('confirmed_at')->count();
        $positiveFeedback = $this->visits->where('visitor_reaction', 'liked')->count();
        $portalUrl        = 'https://miportal.homedelvalle.mx/mi-inmueble';

        return new Content(
            view: 'emails.portal.weekly-property-summary',
            with: compact('confirmedVisits', 'positiveFeedback', 'portalUrl'),
        );
    }
}
