<?php

namespace App\Mail\V4\Mailables;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable genérico que renderiza un template almacenado en la tabla email_templates.
 * Usado para previsualizar en el panel de transactional emails.
 */
class DbTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $renderedHtml;

    public function __construct(
        private readonly string $templateName,
        private readonly array  $sampleVars = [],
        private readonly string $previewSubject = '',
    ) {
        $template = EmailTemplate::where('name', $this->templateName)->first();

        if (! $template) {
            $this->renderedHtml = '<p>Template <strong>' . e($this->templateName) . '</strong> no encontrado en base de datos.</p>';
            return;
        }

        // Sustituir variables de muestra
        $html = $template->body;
        foreach ($this->sampleVars as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        $this->renderedHtml = $html;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->previewSubject ?: ('Preview: ' . $this->templateName),
        );
    }

    public function content(): Content
    {
        // Passamos el HTML renderizado como view inline
        return new Content(
            htmlString: $this->renderedHtml,
        );
    }

    /**
     * Render the mailable to an HTML string (para el preview del panel).
     */
    public function render(): string
    {
        return $this->renderedHtml;
    }
}
