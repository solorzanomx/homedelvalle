<?php

namespace Tests\Feature\Mail\V4;

use App\Mail\V4\Data\LeadInternoData;
use App\Mail\V4\Mailables\LeadInternoMail;
use Tests\TestCase;

class LeadInternoMailTest extends TestCase
{
    public function test_mailable_has_correct_subject(): void
    {
        $data = new LeadInternoData(
            nombre: 'Juan Pérez',
            email: 'juan@example.com',
            telefono: '+52 55 1234 5678',
            origen: 'Contacto web',
            fecha: now()->format('Y-m-d H:i'),
            mensaje: 'Interesado en vender'
        );

        $mailable = new LeadInternoMail($data);
        $this->assertEquals('Nuevo lead · Juan Pérez', $mailable->envelope()->subject);
    }

    public function test_mailable_renders_with_correct_view(): void
    {
        $data = new LeadInternoData(
            nombre: 'Juan Pérez',
            email: 'juan@example.com',
            telefono: '+52 55 1234 5678',
            origen: 'Contacto web',
            fecha: now()->format('Y-m-d H:i'),
            mensaje: 'Interesado en vender'
        );

        $mailable = new LeadInternoMail($data);
        $this->assertEquals('emails.v4.lead-interno', $mailable->content()->view);
    }

    public function test_mailable_contains_lead_data(): void
    {
        $data = new LeadInternoData(
            nombre: 'Juan Pérez',
            email: 'juan@example.com',
            telefono: '+52 55 1234 5678',
            origen: 'Contacto web',
            fecha: now()->format('Y-m-d H:i'),
            mensaje: 'Interesado en vender'
        );

        $mailable = new LeadInternoMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Juan Pérez', $rendered);
        $this->assertStringContainsString('juan@example.com', $rendered);
        $this->assertStringContainsString('Interesado en vender', $rendered);
    }

    public function test_mailable_contains_action_buttons(): void
    {
        $data = new LeadInternoData(
            nombre: 'Juan Pérez',
            email: 'juan@example.com',
            telefono: '+52 55 1234 5678',
            origen: 'Contacto web',
            fecha: now()->format('Y-m-d H:i'),
            mensaje: 'Interesado en vender'
        );

        $mailable = new LeadInternoMail($data);
        $rendered = $mailable->render();

        $this->assertStringContainsString('Ver en CRM', $rendered);
        $this->assertStringContainsString('Responder', $rendered);
    }
}
