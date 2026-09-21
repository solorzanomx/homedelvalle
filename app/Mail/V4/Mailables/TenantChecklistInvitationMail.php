<?php

namespace App\Mail\V4\Mailables;

use App\Support\TenantDocumentChecklist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Enviar checklist de requisitos para rentar" — botón en la ficha del
 * cliente/lead (2026-09-21). Manda la lista de documentos que se piden
 * para calificar como inquilino (App\Support\TenantDocumentChecklist) junto
 * con el link al portal, donde suben cada documento y ven su avance. Al
 * entrar por primera vez, el portal les exige aceptar el Aviso de
 * Privacidad y el Acuerdo de Confidencialidad antes de continuar.
 */
class TenantChecklistInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $nombre,
        public readonly string $portalUrl,
        public readonly bool $isNewAccount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Requisitos para tu renta — Home del Valle');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.tenant-checklist-invitation',
            with: ['checklist' => TenantDocumentChecklist::clientFacing()],
        );
    }
}
