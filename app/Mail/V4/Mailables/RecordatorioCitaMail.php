<?php

namespace App\Mail\V4\Mailables;

use App\Mail\V4\Data\RecordatorioCitaData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioCitaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly RecordatorioCitaData $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Recordatorio: Tu visita de hoy a las {$this->data->hora}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.recordatorio-cita',
            with: [
                'data'     => $this->data,
                'logoUrl'  => $this->getLogoUrl(),
                'iconBase' => rtrim(url('img/email'), '/') . '/',
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
