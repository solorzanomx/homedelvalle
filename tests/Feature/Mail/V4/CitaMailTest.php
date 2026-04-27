<?php

namespace Tests\Feature\Mail\V4;

use App\Mail\V4\Data\CitaData;
use App\Mail\V4\Mailables\CitaMail;
use Tests\TestCase;

class CitaMailTest extends TestCase
{
    public function test_mailable_has_correct_subject(): void
    {
        $data = new CitaData(
            email: 'cliente@example.com',
            dia_semana: 'Lunes',
            dia: '15',
            mes: 'abril',
            anio: '2026',
            hora: '10:00 AM',
            duracion: '30',
            direccion: 'Paseo de los Tamarindos 400',
            colonia: 'Bosques de las Lomas',
            asesor: 'María García'
        );

        $mailable = new CitaMail($data);
        $this->assertStringContainsString('Tu visita está agendada', $mailable->envelope()->subject);
    }

    public function test_mailable_renders_with_correct_view(): void
    {
        $data = new CitaData(
            email: 'cliente@example.com',
            dia_semana: 'Lunes',
            dia: '15',
            mes: 'abril',
            anio: '2026',
            hora: '10:00 AM',
            duracion: '30',
            direccion: 'Paseo de los Tamarindos 400',
            colonia: 'Bosques de las Lomas',
            asesor: 'María García'
        );

        $mailable = new CitaMail($data);
        $this->assertEquals('emails.v4.cita', $mailable->content()->view);
    }

    public function test_mailable_contains_appointment_details(): void
    {
        $data = new CitaData(
            email: 'cliente@example.com',
            dia_semana: 'Lunes',
            dia: '15',
            mes: 'abril',
            anio: '2026',
            hora: '10:00 AM',
            duracion: '30',
            direccion: 'Paseo de los Tamarindos 400',
            colonia: 'Bosques de las Lomas',
            asesor: 'María García'
        );

        $mailable = new CitaMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Paseo de los Tamarindos 400', $rendered);
        $this->assertStringContainsString('Bosques de las Lomas', $rendered);
        $this->assertStringContainsString('María García', $rendered);
        $this->assertStringContainsString('10:00 AM', $rendered);
    }

    public function test_mailable_contains_action_buttons(): void
    {
        $data = new CitaData(
            email: 'cliente@example.com',
            dia_semana: 'Lunes',
            dia: '15',
            mes: 'abril',
            anio: '2026',
            hora: '10:00 AM',
            duracion: '30',
            direccion: 'Paseo de los Tamarindos 400',
            colonia: 'Bosques de las Lomas',
            asesor: 'María García'
        );

        $mailable = new CitaMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Agregar al calendario', $rendered);
        $this->assertStringContainsString('Reagendar', $rendered);
    }
}
