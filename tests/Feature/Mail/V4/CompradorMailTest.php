<?php

namespace Tests\Feature\Mail\V4;

use App\Mail\V4\Data\CompradorData;
use App\Mail\V4\Mailables\CompradorMail;
use Tests\TestCase;

class CompradorMailTest extends TestCase
{
    public function test_mailable_has_correct_subject(): void
    {
        $data = new CompradorData(
            email: 'cliente@example.com',
            colonia: 'Benito Juárez',
            titulo: 'Casa moderna con jardín',
            metros: '350',
            recamaras: '3',
            banos: '2',
            estacionamientos: '2',
            precio: '4500000'
        );

        $mailable = new CompradorMail($data);
        $this->assertStringContainsString('Benito Juárez', $mailable->envelope()->subject);
        $this->assertStringContainsString('Casa moderna con jardín', $mailable->envelope()->subject);
    }

    public function test_mailable_renders_with_correct_view(): void
    {
        $data = new CompradorData(
            email: 'cliente@example.com',
            colonia: 'Benito Juárez',
            titulo: 'Casa moderna con jardín',
            metros: '350',
            recamaras: '3',
            banos: '2',
            estacionamientos: '2',
            precio: '4500000'
        );

        $mailable = new CompradorMail($data);
        $this->assertEquals('emails.v4.comprador', $mailable->content()->view);
    }

    public function test_mailable_contains_property_details(): void
    {
        $data = new CompradorData(
            email: 'cliente@example.com',
            colonia: 'Benito Juárez',
            titulo: 'Casa moderna con jardín',
            metros: '350',
            recamaras: '3',
            banos: '2',
            estacionamientos: '2',
            precio: '4500000'
        );

        $mailable = new CompradorMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Benito Juárez', $rendered);
        $this->assertStringContainsString('Casa moderna con jardín', $rendered);
        $this->assertStringContainsString('350', $rendered);
        $this->assertStringContainsString('3', $rendered); // recámaras
    }

    public function test_mailable_contains_action_buttons(): void
    {
        $data = new CompradorData(
            email: 'cliente@example.com',
            colonia: 'Benito Juárez',
            titulo: 'Casa moderna con jardín',
            metros: '350',
            recamaras: '3',
            banos: '2',
            estacionamientos: '2',
            precio: '4500000'
        );

        $mailable = new CompradorMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Agendar visita', $rendered);
        $this->assertStringContainsString('Ver ficha completa', $rendered);
    }
}
