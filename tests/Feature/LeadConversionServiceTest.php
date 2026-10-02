<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FormSubmission;
use App\Models\Interaction;
use App\Services\LeadConversionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Caso real que lo motivó (2026-10-01, Yarlin): un contacto llegó por DOS formularios distintos
 * para el mismo depa (alta manual + el correo real de Inmuebles24). Se convirtió uno; el otro —
 * y la visita YA CONFIRMADA con feedback real que colgaba de él — quedaron huérfanos, invisibles
 * en la ficha del cliente nuevo, y nadie sabía qué inmueble le interesaba sin ir a buscarlo a mano.
 *
 * LeadConversionService (usado por FormSubmissionsTable::convertToClient y
 * Admin\FormSubmissionController::convertToClient) corrige esto: adopta cualquier lead sin
 * cliente del mismo correo/teléfono, reasigna sus interacciones, y detecta la propiedad de
 * interés desde el payload de Inmuebles24.
 */
class LeadConversionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('whatsapp')->nullable();
            $t->string('client_type')->nullable();
            $t->string('lead_temperature')->nullable();
            $t->decimal('budget_min', 12, 2)->nullable();
            $t->decimal('budget_max', 12, 2)->nullable();
            $t->string('property_type')->nullable();
            $t->json('interest_types')->nullable();
            $t->string('utm_source')->nullable();
            $t->string('utm_medium')->nullable();
            $t->string('utm_campaign')->nullable();
            $t->string('lead_source')->nullable();
            $t->text('initial_notes')->nullable();
            $t->timestamps();
        });
        Schema::create('properties', function (Blueprint $t) {
            $t->id();
            $t->string('title')->nullable();
            $t->unsignedBigInteger('client_id')->nullable();
            $t->timestamps();
        });
        Schema::create('form_submissions', function (Blueprint $t) {
            $t->id();
            $t->string('form_type');
            $t->string('full_name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->unsignedBigInteger('client_id')->nullable();
            $t->decimal('budget_min', 12, 2)->nullable();
            $t->decimal('budget_max', 12, 2)->nullable();
            $t->string('property_type')->nullable();
            $t->json('interest_types')->nullable();
            $t->string('lead_temperature')->nullable();
            $t->string('utm_source')->nullable();
            $t->string('utm_medium')->nullable();
            $t->string('utm_campaign')->nullable();
            $t->json('payload')->nullable();
            $t->timestamps();
        });
        // Crear un FormSubmission dispara el evento FormSubmitted -> listeners que consultan
        // email_settings (SendAcuseMail, SendLeadInternoMail) — igual que en los tests del blog.
        Schema::create('email_settings', fn (Blueprint $t) => $t->id());
        Schema::create('interactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('client_id')->nullable();
            $t->unsignedBigInteger('form_submission_id')->nullable();
            $t->string('type')->nullable();
            $t->string('description')->nullable();
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamps();
        });
    }

    public function test_converting_a_lead_adopts_sibling_leads_and_reparents_their_interactions(): void
    {
        // Mismo contacto, dos leads: uno viejo (con la visita ya confirmada), otro el real que se va a convertir.
        // withoutEvents: crear un FormSubmission normalmente dispara FormSubmitted (acuses por
        // correo) — aquí solo nos interesa la conversión, no esa cadena completa de listeners.
        $oldLead = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type' => 'inmuebles24', 'full_name' => 'Yarlin Nava Garcia',
            'email' => 'yarlin@test.local', 'phone' => '5567037095',
            'payload' => ['alta_manual' => true],
        ]));
        $visit = Interaction::create([
            'form_submission_id' => $oldLead->id, 'type' => 'visit',
            'description' => 'Visita agendada', 'confirmed_at' => now(),
        ]);

        $realLead = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type' => 'inmuebles24', 'full_name' => 'Yarlin Nava',
            'email' => 'yarlin@test.local', 'phone' => '5567037095',
            'interest_types' => ['renta_inquilino'],
            'payload' => ['propiedad_local_id' => 29, 'propiedad_local' => 'Depa de prueba'],
        ]));

        $property = \App\Models\Property::create(['title' => 'Depa de prueba', 'client_id' => 90]);
        // El fixture usa id real 29 en el payload — forzamos el mismo id en la tabla de prueba.
        \Illuminate\Support\Facades\DB::table('properties')->where('id', $property->id)->update(['id' => 29]);

        $result = app(LeadConversionService::class)->convert($realLead);

        $this->assertFalse($result['was_existing']);
        $this->assertSame(1, $result['reparented_leads'], 'Debe adoptar el lead viejo del mismo contacto.');
        $this->assertSame(1, $result['reparented_interactions'], 'Debe reasignar la visita que colgaba del lead viejo.');
        $this->assertSame(29, $result['property_id']);
        $this->assertSame(90, $result['owner_client_id']);

        $oldLead->refresh();
        $visit->refresh();
        $this->assertSame($result['client']->id, $oldLead->client_id, 'El lead viejo debe quedar ligado al mismo cliente.');
        $this->assertSame($result['client']->id, $visit->client_id, 'La visita debe dejar de estar huérfana.');
    }

    public function test_converting_an_already_converted_lead_does_not_duplicate_or_re_run_automation(): void
    {
        $client = Client::create(['name' => 'Alguien', 'email' => 'alguien@test.local']);
        $lead = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type' => 'contacto', 'full_name' => 'Alguien', 'email' => 'alguien@test.local',
            'client_id' => $client->id,
        ]));

        $result = app(LeadConversionService::class)->convert($lead);

        $this->assertTrue($result['was_existing']);
        $this->assertSame($client->id, $result['client']->id);
        $this->assertSame(1, Client::count(), 'No debe crear un cliente duplicado.');
    }

    public function test_never_adopts_a_lead_that_already_belongs_to_another_client(): void
    {
        $otherClient = Client::create(['name' => 'Otro cliente', 'email' => 'otro@test.local']);
        $takenLead = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type' => 'contacto', 'full_name' => 'Coincidencia de teléfono',
            'phone' => '5500001111', 'client_id' => $otherClient->id,
        ]));

        $newLead = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type' => 'contacto', 'full_name' => 'Nombre nuevo', 'phone' => '5500001111',
        ]));

        $result = app(LeadConversionService::class)->convert($newLead);

        $takenLead->refresh();
        $this->assertSame(0, $result['reparented_leads']);
        $this->assertSame($otherClient->id, $takenLead->client_id, 'Un lead ya asignado a otro cliente nunca se debe reasignar.');
        $this->assertNotSame($otherClient->id, $result['client']->id);
    }
}
