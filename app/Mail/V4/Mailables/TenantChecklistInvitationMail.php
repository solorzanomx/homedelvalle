<?php

namespace App\Mail\V4\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Enviar checklist de requisitos para rentar" — botón en la ficha del
 * cliente/lead (2026-09-21). Manda la lista de documentos que se piden para
 * calificar como inquilino (App\Support\TenantDocumentChecklist).
 *
 * Dos modos, según si ya se decidió avanzar (2026-09-21, corrección: NO
 * convertir a cliente solo por mandar el checklist):
 * - $portalUrl null → solo informativo, para un lead que todavía no se
 *   convierte a Client (para que vaya preparando documentos, sin crear
 *   cuenta de portal ni arrancar la investigación).
 * - $portalUrl con valor → ya es Client y se decidió avanzar; incluye el
 *   link real al portal (activación o login) donde sube todo. Al entrar
 *   por primera vez, el portal exige aceptar el Aviso de Privacidad y el
 *   Acuerdo de Confidencialidad antes de continuar.
 */
class TenantChecklistInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $nombre,
        public readonly ?string $portalUrl = null,
        public readonly bool $isNewAccount = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Requisitos para tu renta — Home del Valle');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.v4.tenant-checklist-invitation',
            with: ['requisitosUrl' => route('landing.rentar.requisitos')],
        );
    }
}
