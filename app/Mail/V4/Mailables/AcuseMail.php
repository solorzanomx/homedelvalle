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
            subject: match($this->data->form_type) {
                'vendedor'  => 'Recibimos tu solicitud de valuación · Home del Valle',
                'comprador' => 'Recibimos tu búsqueda · Home del Valle',
                'b2b'       => 'Recibimos tu brief calificador · Home del Valle',
                default     => 'Recibimos tu mensaje · Home del Valle',
            }
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.acuse',
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
