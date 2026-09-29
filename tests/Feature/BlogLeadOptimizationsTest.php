<?php

namespace Tests\Feature;

use App\Models\BlogLeadReminder;
use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/**
 * Optimizaciones post-lanzamiento pedidas por Alejandro tras revisar las 7 fases:
 * Pixel de Meta con eventos reales (no solo PageView), recordatorio automático de leads del blog
 * sin contactar, y la calculadora activada en un segundo post de herencias.
 */
class BlogLeadOptimizationsTest extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
    }

    public function test_meta_pixel_fires_lead_event_not_only_pageview(): void
    {
        $js = file_get_contents(resource_path('views/layouts/public.blade.php'));

        $this->assertStringContainsString("fbq('track', 'Lead'", $js);
        $this->assertStringContainsString("name === 'generate_lead'", $js);
    }

    /**
     * Hallazgo real del QA visual de esta ronda: la calculadora y el form genérico de valuación
     * quedaban uno debajo del otro, pidiendo lo mismo dos veces. Ahora son mutuamente excluyentes.
     */
    public function test_calculator_and_generic_quick_valuation_form_are_mutually_exclusive(): void
    {
        $view = file_get_contents(resource_path('views/blog/show.blade.php'));

        $this->assertMatchesRegularExpression(
            '/@if\(\$post->show_succession_calculator\).*?@else.*?blog-quick-valuation-form.*?@endif/s',
            $view
        );
    }

    /**
     * Otro hallazgo real del QA: agregué `hidden sm:block` a whatsapp-float.blade.php (Fase 3) pero
     * nunca reconstruí el CSS — como nada más en el sitio usaba exactamente "sm:block", Tailwind
     * nunca lo generó y el botón flotante genérico quedó invisible en TODO post con cluster desde
     * que se desplegó la Fase 3. Este test no puede correr Tailwind, pero deja constancia de que el
     * bundle commiteado si trae la clase — si algún día vuelve a faltar, hay que correr `npm run build`.
     */
    public function test_compiled_css_includes_the_class_the_whatsapp_float_needs(): void
    {
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile = public_path('build/' . ($manifest['resources/css/app.css']['file'] ?? ''));

        if (! file_exists($cssFile)) {
            $this->markTestSkipped('No hay build de assets en este entorno.');
        }

        $css = file_get_contents($cssFile);
        $this->assertStringContainsString('.sm\\:block{display:block}', $css, 'Falta reconstruir el CSS (npm run build) — sm:block no está en el bundle.');
    }

    public function test_hermano_no_quiere_vender_post_gets_the_calculator(): void
    {
        $migration = require database_path('migrations/2026_09_30_170000_expand_succession_calculator_optimizations.php');
        $this->assertInstanceOf(\Illuminate\Database\Migrations\Migration::class, $migration);

        // isr-venta NO debe activarse — ese post trata el ISR de la venta, no el costo de la sucesión.
        $source = file_get_contents(database_path('migrations/2026_09_30_170000_expand_succession_calculator_optimizations.php'));
        $this->assertStringContainsString('hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx', $source);
        $this->assertStringNotContainsString('isr-venta-propiedad-heredada-mexico-2026', $source);
    }

    private function setUpReminderSchema(): void
    {
        foreach (['email_settings' => fn($t) => $t->id(), 'legal_documents' => function ($t) {
            $t->id(); $t->string('type')->nullable(); $t->string('status')->nullable(); $t->unsignedBigInteger('current_version_id')->nullable();
        }, 'clients' => function ($t) { $t->id(); $t->string('email')->nullable(); $t->timestamps(); }] as $table => $cb) {
            if (! Schema::hasTable($table)) {
                Schema::create($table, $cb);
            }
        }
        if (! Schema::hasTable('form_submissions')) {
            Schema::create('form_submissions', function ($t) {
                $t->id(); $t->string('form_type'); $t->string('source_page')->nullable(); $t->string('full_name');
                $t->string('email')->nullable(); $t->string('phone')->nullable(); $t->json('payload')->nullable();
                $t->string('lead_tag')->nullable(); $t->string('client_type')->nullable(); $t->string('lead_temperature')->default('warm');
                $t->string('status')->default('new'); $t->string('utm_source')->nullable(); $t->string('utm_medium')->nullable();
                $t->string('utm_campaign')->nullable(); $t->string('referrer')->nullable();
                $t->unsignedBigInteger('landing_post_id')->nullable(); $t->string('landing_label')->nullable();
                $t->string('ip')->nullable(); $t->string('user_agent')->nullable(); $t->unsignedBigInteger('assigned_to')->nullable();
                $t->unsignedBigInteger('client_id')->nullable(); $t->timestamp('contacted_at')->nullable(); $t->timestamp('seen_at')->nullable();
                $t->text('notes')->nullable(); $t->timestamps();
            });
        }
        if (! Schema::hasTable('blog_lead_reminders')) {
            Artisan::call('migrate', ['--force' => true, '--path' => [
                'database/migrations/2026_09_30_170001_create_blog_lead_reminders_table.php',
            ]]);
        }
        if (! Schema::hasTable('users')) {
            Schema::create('users', function ($t) {
                $t->id(); $t->string('name')->nullable(); $t->string('email')->nullable();
                $t->string('password')->nullable(); $t->string('role')->nullable(); $t->timestamps();
            });
        }
        foreach (['email', 'role'] as $col) {
            if (! Schema::hasColumn('users', $col)) {
                Schema::table('users', fn($t) => $t->string($col)->nullable());
            }
        }
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function ($t) {
                $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('from_user_id')->nullable();
                $t->string('type'); $t->string('title')->nullable(); $t->text('body')->nullable();
                $t->json('data')->nullable(); $t->timestamp('read_at')->nullable(); $t->timestamps();
            });
        }
    }

    public function test_reminds_only_stale_uncontacted_blog_leads_at_7_and_14_days(): void
    {
        Mail::fake();
        $this->setUpReminderSchema();
        User::forceCreate(['name' => 'Admin', 'email' => 'admin@test.local', 'role' => 'admin']);

        $stale = FormSubmission::create(['form_type' => 'vendedor_predio', 'full_name' => 'Viejo', 'source_page' => '/blog/x', 'phone' => '5500001111', 'payload' => ['origen' => 'blog_cta'], 'status' => 'new']);
        $stale->forceFill(['created_at' => now()->subDays(8)])->save();

        $recent = FormSubmission::create(['form_type' => 'vendedor_predio', 'full_name' => 'Reciente', 'source_page' => '/blog/x', 'phone' => '5500002222', 'payload' => ['origen' => 'blog_cta'], 'status' => 'new']);
        $recent->forceFill(['created_at' => now()->subDays(2)])->save();

        $contacted = FormSubmission::create(['form_type' => 'vendedor_predio', 'full_name' => 'Contactado', 'source_page' => '/blog/x', 'phone' => '5500003333', 'payload' => ['origen' => 'blog_cta'], 'status' => 'contacted']);
        $contacted->forceFill(['created_at' => now()->subDays(10)])->save();

        $notBlog = FormSubmission::create(['form_type' => 'contacto', 'full_name' => 'No blog', 'source_page' => '/contacto', 'phone' => '5500004444', 'payload' => ['origen' => 'contacto_general'], 'status' => 'new']);
        $notBlog->forceFill(['created_at' => now()->subDays(10)])->save();

        $this->artisan('blog:remind-stale-leads')->assertSuccessful();

        $this->assertTrue(BlogLeadReminder::where('form_submission_id', $stale->id)->where('tier', 'd7')->exists());
        $this->assertFalse(BlogLeadReminder::where('form_submission_id', $recent->id)->exists());
        $this->assertFalse(BlogLeadReminder::where('form_submission_id', $contacted->id)->exists());
        $this->assertFalse(BlogLeadReminder::where('form_submission_id', $notBlog->id)->exists());
        $this->assertGreaterThan(0, Notification::where('type', 'blog_lead_reminder')->count());

        // Correrlo dos veces no debe duplicar el recordatorio de la misma etapa.
        $countBefore = Notification::where('type', 'blog_lead_reminder')->count();
        $this->artisan('blog:remind-stale-leads');
        $this->assertSame($countBefore, Notification::where('type', 'blog_lead_reminder')->count());
    }
}
