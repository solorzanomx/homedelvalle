<?php

namespace Tests\Feature;

use App\Livewire\Blog\SuccessionCalculator;
use App\Models\FormSubmission;
use App\Models\SuccessionCalculatorConfig;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/** Fase 4 del prompt de leads del blog (docs/funcionalidades/blog-calculadora-sucesion.md). */
class BlogSuccessionCalculatorTest extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
        if (! \Illuminate\Support\Facades\Schema::hasTable('succession_calculator_configs')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--path' => [
                'database/migrations/2026_09_30_100000_create_succession_calculator_configs_table.php',
            ]]);
        }
        foreach (['email_settings' => fn($t) => $t->id(), 'legal_documents' => function ($t) {
            $t->id(); $t->string('type')->nullable(); $t->string('status')->nullable(); $t->unsignedBigInteger('current_version_id')->nullable();
        }] as $table => $cb) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\Schema::create($table, $cb);
            }
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('clients')) {
            \Illuminate\Support\Facades\Schema::create('clients', function ($t) {
                $t->id(); $t->string('email')->nullable(); $t->timestamps();
            });
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('form_submissions')) {
            \Illuminate\Support\Facades\Schema::create('form_submissions', function ($t) {
                $t->id();
                $t->string('form_type'); $t->string('source_page')->nullable(); $t->string('full_name');
                $t->string('email')->nullable(); $t->string('phone'); $t->json('payload')->nullable();
                $t->string('lead_tag')->nullable(); $t->string('client_type')->nullable();
                $t->string('lead_temperature')->default('warm'); $t->string('status')->default('new');
                $t->string('utm_source')->nullable(); $t->string('utm_medium')->nullable(); $t->string('utm_campaign')->nullable();
                $t->string('referrer')->nullable(); $t->unsignedBigInteger('landing_post_id')->nullable(); $t->string('landing_label')->nullable();
                $t->string('ip')->nullable(); $t->string('user_agent')->nullable(); $t->unsignedBigInteger('assigned_to')->nullable();
                $t->unsignedBigInteger('client_id')->nullable(); $t->timestamp('contacted_at')->nullable(); $t->timestamp('seen_at')->nullable();
                $t->text('notes')->nullable(); $t->timestamps();
            });
        }
    }

    public function test_seed_has_both_scenarios_unvalidated(): void
    {
        $this->assertSame(2, SuccessionCalculatorConfig::count());
        foreach ([SuccessionCalculatorConfig::CON_TESTAMENTO, SuccessionCalculatorConfig::SIN_TESTAMENTO] as $scenario) {
            $config = SuccessionCalculatorConfig::forScenario($scenario);
            $this->assertNotNull($config);
            $this->assertFalse($config->validated, "El seed de {$scenario} no debe llegar 'validado' — son placeholders.");
        }
    }

    public function test_estimate_never_uses_numbers_outside_the_configured_ranges(): void
    {
        $config = SuccessionCalculatorConfig::forScenario(SuccessionCalculatorConfig::CON_TESTAMENTO);
        $estimate = $config->estimate(1000000, 1, true);

        $notarial = $estimate['items'][0];
        $this->assertEqualsWithDelta($config->notarial_pct_min * 10000, $notarial['min'], 0.01);
        $this->assertEqualsWithDelta($config->notarial_pct_max * 10000, $notarial['max'], 0.01);
        $this->assertFalse($estimate['validated']);
    }

    public function test_extra_heirs_and_missing_title_add_their_own_line_items(): void
    {
        $config = SuccessionCalculatorConfig::forScenario(SuccessionCalculatorConfig::SIN_TESTAMENTO);

        $base = $config->estimate(2000000, 1, true);
        $withHeirs = $config->estimate(2000000, 4, true);
        $withoutTitle = $config->estimate(2000000, 1, false);

        $this->assertGreaterThan($base['total_min'], $withHeirs['total_min'], '3 herederos extra deben subir el total');
        $this->assertGreaterThan($base['total_min'], $withoutTitle['total_min'], 'Sin escrituras debe agregar el costo de regularización');
        $this->assertCount(count($base['items']) + 1, $withHeirs['items']);
        $this->assertCount(count($base['items']) + 1, $withoutTitle['items']);
    }

    public function test_calculator_start_fires_on_first_input_only_once(): void
    {
        $post = $this->makePost(['slug' => 'cuanto-cuesta-sucesion-cdmx-2026-test']);

        $lw = Livewire::test(SuccessionCalculator::class, ['postId' => $post->id]);
        $this->assertFalse($lw->get('started'));
        $lw->set('valorInmueble', '2000000');
        $this->assertTrue($lw->get('started'));
        $lw->assertDispatched('calculator-event', stage: 'start');
    }

    /**
     * Optimización post-lanzamiento: calcular y capturar el lead ahora son UN SOLO paso (antes se
     * pedía el WhatsApp DESPUÉS de mostrar el resultado — quien se iba sin dejarlo quedaba
     * completamente perdido). Un solo submit valida todo, calcula, crea el lead y muestra el
     * resultado — sin una promesa de "en 24h", la respuesta sigue siendo instantánea.
     */
    public function test_calculate_validates_calculator_and_contact_fields_together(): void
    {
        $post = $this->makePost(['slug' => 'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026-test']);

        Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '2500000')
            ->set('conTestamento', 'no')
            ->set('numHerederos', 2)
            ->set('tieneEscrituras', 'no')
            ->set('aviso', true)
            ->call('calculate')
            ->assertHasErrors('whatsapp')   // sin WhatsApp no calcula NI captura
            ->assertSet('calculated', false);
    }

    public function test_calculate_creates_the_lead_and_shows_the_result_in_the_same_step(): void
    {
        Mail::fake();
        if (! \Illuminate\Support\Facades\Schema::hasColumn('site_settings', 'whatsapp_number')) {
            \Illuminate\Support\Facades\Schema::table('site_settings', fn($t) => $t->string('whatsapp_number')->nullable());
        }
        \App\Models\SiteSetting::query()->exists() || \App\Models\SiteSetting::create([]);
        \App\Models\SiteSetting::query()->update(['whatsapp_number' => '5511112222']);
        $post = $this->makePost(['slug' => 'post-calc-lead']);

        $lw = Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '3000000')
            ->set('conTestamento', 'si')
            ->set('numHerederos', 1)
            ->set('tieneEscrituras', 'si')
            ->set('whatsapp', '5511119999')
            ->set('colonia', 'Del Valle Centro')
            ->set('aviso', true)
            ->call('calculate')
            ->assertSet('calculated', true)
            ->assertDispatched('calculator-event', stage: 'complete', conTestamento: 'si', validated: '0')
            ->assertDispatched('lead-conversion');

        $lead = FormSubmission::where('phone', '5511119999')->first();
        $this->assertNotNull($lead);
        $this->assertNull($lead->email);   // no lo dieron — opcional
        $this->assertSame('blog_calculadora_sucesion', $lead->payload['origen']);
        $this->assertEquals(3000000, $lead->payload['valor_inmueble']);
        $this->assertSame('Del Valle Centro', $lead->payload['colonia']);
        $this->assertArrayHasKey('estimado_min', $lead->payload);
        $this->assertArrayHasKey('estimado_max', $lead->payload);
        $this->assertNotNull($lw->get('whatsappContinueUrl'));
        $this->assertNotNull($lw->get('result'));   // el desglose sigue mostrándose, no se gatea
    }

    /**
     * Hallazgo 2026-09-30: leads con teléfonos obviamente falsos (ej. "1111111111") y sin ninguna
     * forma de saber si eran de Benito Juárez o no. La colonia es obligatoria (catálogo real de
     * MarketZone/MarketColonia, + "Otra colonia (fuera de Benito Juárez)" como catch-all) y el
     * WhatsApp rechaza patrones evidentes de número falso.
     */
    public function test_colonia_is_required_and_accepts_the_real_catalog_or_fuera_de_bj(): void
    {
        Mail::fake();
        $post = $this->makePost(['slug' => 'post-calc-colonia']);

        Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '2000000')->set('conTestamento', 'si')->set('numHerederos', 1)
            ->set('tieneEscrituras', 'si')->set('whatsapp', '5511116666')->set('aviso', true)
            ->call('calculate')
            ->assertHasErrors('colonia');

        Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '2000000')->set('conTestamento', 'si')->set('numHerederos', 1)
            ->set('tieneEscrituras', 'si')->set('whatsapp', '5511116665')
            ->set('colonia', 'colonia inventada que no existe')->set('aviso', true)
            ->call('calculate')
            ->assertHasErrors('colonia');

        Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '2000000')->set('conTestamento', 'si')->set('numHerederos', 1)
            ->set('tieneEscrituras', 'si')->set('whatsapp', '5511116664')
            ->set('colonia', \App\Support\BenitoJuarezColonias::FUERA_DE_BJ)->set('aviso', true)
            ->call('calculate')
            ->assertHasNoErrors('colonia');

        $lead = FormSubmission::where('phone', '5511116664')->first();
        $this->assertSame(\App\Support\BenitoJuarezColonias::FUERA_DE_BJ, $lead->payload['colonia']);
    }

    public function test_calculate_rejects_obviously_fake_phone_numbers(): void
    {
        $post = $this->makePost(['slug' => 'post-calc-telefono-falso']);

        foreach (['1111111111', '1234567890', '0123456789', '9876543210'] as $fake) {
            Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
                ->set('valorInmueble', '2000000')->set('conTestamento', 'si')->set('numHerederos', 1)
                ->set('tieneEscrituras', 'si')->set('whatsapp', $fake)
                ->set('colonia', 'Del Valle Centro')->set('aviso', true)
                ->call('calculate')
                ->assertHasErrors('whatsapp');
        }

        $this->assertDatabaseMissing('form_submissions', ['phone' => '1111111111']);
    }

    public function test_optional_email_is_saved_and_wired_to_automation_engine(): void
    {
        Mail::fake();
        $post = $this->makePost(['slug' => 'post-calc-email']);

        Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '2000000')
            ->set('conTestamento', 'no')
            ->set('numHerederos', 1)
            ->set('tieneEscrituras', 'si')
            ->set('whatsapp', '5511118888')
            ->set('colonia', 'Narvarte Poniente')
            ->set('email', 'prospecto@correo.com')
            ->set('aviso', true)
            ->call('calculate');

        $lead = FormSubmission::where('phone', '5511118888')->first();
        $this->assertSame('prospecto@correo.com', $lead->email);
    }

    public function test_honeypot_blocks_silently_without_creating_a_lead(): void
    {
        $post = $this->makePost(['slug' => 'post-calc-honeypot']);

        Livewire::test(SuccessionCalculator::class, ['postId' => $post->id])
            ->set('valorInmueble', '2000000')
            ->set('conTestamento', 'si')
            ->set('numHerederos', 1)
            ->set('tieneEscrituras', 'si')
            ->set('whatsapp', '5511117777')
            ->set('colonia', 'Del Valle Centro')
            ->set('aviso', true)
            ->set('website_url', 'soy un bot')
            ->call('calculate');

        $this->assertDatabaseMissing('form_submissions', ['phone' => '5511117777']);
    }

    public function test_admin_routes_exist_and_require_staff(): void
    {
        $this->assertTrue(Route::has('admin.succession-calculator.index'));
        $this->assertTrue(Route::has('admin.succession-calculator.update'));
        $this->assertContains('viewer', Route::getRoutes()->getByName('admin.succession-calculator.index')->gatherMiddleware());
    }
}
