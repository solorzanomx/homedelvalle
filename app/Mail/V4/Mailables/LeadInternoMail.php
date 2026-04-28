<?php

namespace App\Mail\V4\Mailables;

use App\Mail\V4\Data\LeadInternoData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadInternoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly LeadInternoData $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Nuevo lead · {$this->data->nombre}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.lead-interno',
            with: [
                'data' => $this->data,
                'logoUrl' => $this->getLogoUrl(),
            ]
        );
    }

    private function getLogoUrl(): ?string
    {
        try {
            $settings = \App\Models\SiteSetting::current();
            if ($settings?->logo_path) {
                $url = \Illuminate\Support\Facades\Storage::url($settings->logo_path);
                return url($url);
            }
        } catch (\Throwable $e) {
            // Si hay error, retorna null y usa fallback
        }
        return null;
    }
}
