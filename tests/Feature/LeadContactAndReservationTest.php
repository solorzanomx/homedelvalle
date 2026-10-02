<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\FormSubmissionController;
use App\Http\Controllers\RentalProcessController;
use App\Models\Client;
use App\Models\FormSubmission;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Dos hallazgos reales 2026-10-02, en la misma sesión que trabajó el expediente de Yarlin:
 *
 * 1. El botón "Responder por WhatsApp" era un link directo a wa.me — nunca marcaba el lead como
 *    contactado. Con la mayoría del contacto real siendo por WhatsApp, un lead podía llevar días
 *    de conversación y seguir viéndose "Nuevo" / "Contactado: —" en el panel.
 * 2. Crear un trato de renta nunca tocaba Property.status — la propiedad se quedaba "disponible"
 *    (visible en el sitio público, en el buscador) aunque ya tuviera un trato real en curso.
 */
class LeadContactAndReservationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
        });
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->timestamps();
        });
        Schema::create('brokers', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->timestamps();
        });
        Schema::create('properties', function (Blueprint $t) {
            $t->id();
            $t->string('title')->nullable();
            $t->string('property_type')->nullable();
            $t->string('operation_type')->nullable();
            $t->string('status')->default('available');
            $t->unsignedBigInteger('client_id')->nullable();
            $t->decimal('price', 14, 2)->nullable();
            $t->string('currency', 10)->default('MXN');
            $t->timestamps();
        });
        // rental_processes: migración real de creación — mismas columnas que usa
        // RentalProcessController::store(), sin las "extend_*" posteriores (no las necesita este test).
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--path' => [
            'database/migrations/2026_04_01_200001_create_rental_processes_table.php',
        ]]);
        Schema::create('rental_stage_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('rental_process_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('from_stage')->nullable();
            $t->string('to_stage')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('form_submissions', function (Blueprint $t) {
            $t->id();
            $t->string('form_type');
            $t->string('full_name');
            $t->string('source_page')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->json('payload')->nullable();
            $t->string('status')->default('new');
            $t->unsignedBigInteger('client_id')->nullable();
            $t->timestamp('contacted_at')->nullable();
            $t->timestamps();
        });
    }

    public function test_whatsapp_redirect_marks_the_lead_as_contacted_once(): void
    {
        $lead = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type' => 'inmuebles24', 'full_name' => 'Yarlin Nava', 'source_page' => 'inmuebles24:test',
            'phone' => '5567037095', 'payload' => [], 'status' => 'new',
        ]));

        $this->assertNull($lead->contacted_at);
        $this->assertSame('new', $lead->status);

        $controller = app(FormSubmissionController::class);
        $response = $controller->whatsappRedirect($lead);

        $this->assertStringStartsWith('https://wa.me/5567037095', $response->getTargetUrl());

        $lead->refresh();
        $this->assertNotNull($lead->contacted_at, 'Debe marcar contacted_at al abrir WhatsApp.');
        $this->assertSame('contacted', $lead->status, 'Debe pasar de "new" a "contacted".');

        // Segunda llamada: no debe pisar el contacted_at ya puesto.
        $primerContacto = $lead->contacted_at;
        $controller->whatsappRedirect($lead->fresh());
        $lead->refresh();
        $this->assertTrue($lead->contacted_at->eq($primerContacto), 'No debe pisar un contacted_at ya existente.');
    }

    public function test_rental_process_creation_reserves_the_property_when_checkbox_is_on(): void
    {
        [$owner, $tenant, $property] = $this->makeRentalFixtures();

        $this->assertSame('available', $property->status);

        $request = Request::create('/rentals', 'POST', [
            'property_id' => $property->id,
            'owner_client_id' => $owner->id,
            'tenant_client_id' => $tenant->id,
            'mark_property_reserved' => '1',
        ]);

        app(RentalProcessController::class)->store($request);

        $property->refresh();
        $this->assertSame('reserved', $property->status);
    }

    public function test_rental_process_creation_leaves_the_property_available_when_checkbox_is_off(): void
    {
        [$owner, $tenant, $property] = $this->makeRentalFixtures();

        $request = Request::create('/rentals', 'POST', [
            'property_id' => $property->id,
            'owner_client_id' => $owner->id,
            'tenant_client_id' => $tenant->id,
            // sin mark_property_reserved — simula el checkbox desmarcado (no se manda)
        ]);

        app(RentalProcessController::class)->store($request);

        $property->refresh();
        $this->assertSame('available', $property->status, 'Sin el checkbox marcado, la propiedad no debe reservarse sola.');
    }

    /** @return array{0: Client, 1: Client, 2: Property} */
    private function makeRentalFixtures(): array
    {
        $user = User::forceCreate(['name' => 'Agente de prueba', 'email' => 'agente@test.local']);
        Auth::login($user);

        $owner = Client::create(['name' => 'Propietario de prueba']);
        $tenant = Client::create(['name' => 'Inquilino de prueba']);
        $property = Property::create([
            'title' => 'Depa de prueba', 'property_type' => 'Apartment', 'operation_type' => 'rental',
            'status' => 'available', 'client_id' => $owner->id, 'price' => 20000, 'currency' => 'MXN',
        ]);

        return [$owner, $tenant, $property];
    }
}
