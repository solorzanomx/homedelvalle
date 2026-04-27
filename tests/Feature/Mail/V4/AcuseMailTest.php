<?php

namespace Tests\Feature\Mail\V4;

use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Mailables\AcuseMail;
use Tests\TestCase;

class AcuseMailTest extends TestCase
{
    public function test_mailable_has_correct_subject(): void
    {
        $data = new AcuseData(
            folio: 'lead-123456',
            email: 'cliente@example.com'
        );

        $mailable = new AcuseMail($data);
        $this->assertEquals('Recibimos tu mensaje', $mailable->envelope()->subject);
    }

    public function test_mailable_renders_with_correct_view(): void
    {
        $data = new AcuseData(
            folio: 'lead-123456',
            email: 'cliente@example.com'
        );

        $mailable = new AcuseMail($data);
        $this->assertEquals('emails.v4.acuse', $mailable->content()->view);
    }

    public function test_mailable_contains_folio(): void
    {
        $data = new AcuseData(
            folio: 'lead-123456',
            email: 'cliente@example.com'
        );

        $mailable = new AcuseMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Folio', $rendered);
        $this->assertStringContainsString('HDV-', $rendered);
    }

    public function test_mailable_contains_action_buttons(): void
    {
        $data = new AcuseData(
            folio: 'lead-123456',
            email: 'cliente@example.com'
        );

        $mailable = new AcuseMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Ver propiedades', $rendered);
        $this->assertStringContainsString('Responder', $rendered);
    }
}
