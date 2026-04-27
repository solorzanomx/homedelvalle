<?php

namespace Tests\Feature\Mail\V4;

use App\Mail\V4\Data\BienvenidaData;
use App\Mail\V4\Mailables\BienvenidaMail;
use Tests\TestCase;

class BienvenidaMailTest extends TestCase
{
    public function test_mailable_has_correct_subject(): void
    {
        $data = new BienvenidaData(
            email: 'cliente@example.com',
            usuario: 'juan.perez@homedelvalle.com',
            password_temporal: 'Temp123!@#',
            url_acceso: 'https://app.homedelvalle.mx/login'
        );

        $mailable = new BienvenidaMail($data);
        $this->assertEquals('Bienvenido al área de clientes', $mailable->envelope()->subject);
    }

    public function test_mailable_renders_with_correct_view(): void
    {
        $data = new BienvenidaData(
            email: 'cliente@example.com',
            usuario: 'juan.perez@homedelvalle.com',
            password_temporal: 'Temp123!@#',
            url_acceso: 'https://app.homedelvalle.mx/login'
        );

        $mailable = new BienvenidaMail($data);
        $this->assertEquals('emails.v4.bienvenida', $mailable->content()->view);
    }

    public function test_mailable_contains_credentials(): void
    {
        $data = new BienvenidaData(
            email: 'cliente@example.com',
            usuario: 'juan.perez@homedelvalle.com',
            password_temporal: 'Temp123!@#',
            url_acceso: 'https://app.homedelvalle.mx/login'
        );

        $mailable = new BienvenidaMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('juan.perez@homedelvalle.com', $rendered);
        $this->assertStringContainsString('Temp123!@#', $rendered);
        $this->assertStringContainsString('Tus credenciales', $rendered);
    }

    public function test_mailable_contains_action_button(): void
    {
        $data = new BienvenidaData(
            email: 'cliente@example.com',
            usuario: 'juan.perez@homedelvalle.com',
            password_temporal: 'Temp123!@#',
            url_acceso: 'https://app.homedelvalle.mx/login'
        );

        $mailable = new BienvenidaMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Ingresar al área de clientes', $rendered);
        $this->assertStringContainsString('https://app.homedelvalle.mx/login', $rendered);
    }

    public function test_mailable_contains_security_notice(): void
    {
        $data = new BienvenidaData(
            email: 'cliente@example.com',
            usuario: 'juan.perez@homedelvalle.com',
            password_temporal: 'Temp123!@#',
            url_acceso: 'https://app.homedelvalle.mx/login'
        );

        $mailable = new BienvenidaMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('¿Problemas para entrar?', $rendered);
        $this->assertStringContainsString('Nunca pediremos tu contraseña', $rendered);
    }
}
