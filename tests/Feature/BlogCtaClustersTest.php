<?php

namespace Tests\Feature;

use App\Models\BlogCtaConfig;
use App\Models\FormSubmission;
use App\Models\PostCategory;
use App\Support\BlogCluster;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/** Fase 3 del prompt de leads del blog (docs/funcionalidades/blog-cta-clusters.md). */
class BlogCtaClustersTest extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
        if (! \Illuminate\Support\Facades\Schema::hasTable('blog_cta_configs')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--path' => [
                'database/migrations/2026_09_29_150001_create_blog_cta_configs_table.php',
            ]]);
        }
        // form_submissions no vive en el esquema mínimo del blog — se crea suelta, solo con lo
        // que este test necesita (igual que las demás tablas del trait).
        if (! \Illuminate\Support\Facades\Schema::hasTable('email_settings')) {
            \Illuminate\Support\Facades\Schema::create('email_settings', fn($t) => $t->id());
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('legal_documents')) {
            \Illuminate\Support\Facades\Schema::create('legal_documents', function ($t) {
                $t->id(); $t->string('type')->nullable(); $t->string('status')->nullable();
                $t->unsignedBigInteger('current_version_id')->nullable();
            });
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('clients')) {
            \Illuminate\Support\Facades\Schema::create('clients', function ($t) {
                $t->id(); $t->string('email')->nullable(); $t->timestamps();
            });
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('form_submissions')) {
            \Illuminate\Support\Facades\Schema::create('form_submissions', function ($t) {
                $t->id();
                $t->string('form_type');
                $t->string('source_page')->nullable();
                $t->string('full_name');
                $t->string('email')->nullable();
                $t->string('phone');
                $t->json('payload')->nullable();
                $t->string('lead_tag')->nullable();
                $t->string('client_type')->nullable();
                $t->string('lead_temperature')->default('warm');
                $t->string('status')->default('new');
                $t->string('utm_source')->nullable();
                $t->string('utm_medium')->nullable();
                $t->string('utm_campaign')->nullable();
                $t->string('referrer')->nullable();
                $t->unsignedBigInteger('landing_post_id')->nullable();
                $t->string('landing_label')->nullable();
                $t->string('ip')->nullable();
                $t->string('user_agent')->nullable();
                $t->unsignedBigInteger('assigned_to')->nullable();
                $t->unsignedBigInteger('client_id')->nullable();
                $t->timestamp('contacted_at')->nullable();
                $t->timestamp('seen_at')->nullable();
                $t->text('notes')->nullable();
                $t->timestamps();
            });
        }
    }

    public function test_cluster_config_seed_has_all_five_clusters_with_copy(): void
    {
        $this->assertSame(5, BlogCtaConfig::count());
        foreach (array_keys(BlogCluster::LABELS) as $cluster) {
            $config = BlogCtaConfig::forCluster($cluster);
            $this->assertNotNull($config, "Falta la config del cluster {$cluster}");
            $this->assertNotEmpty($config->headline);
            $this->assertNotEmpty($config->whatsapp_message);
        }
    }

    public function test_herencias_shows_sell_variant_only_on_decided_slugs(): void
    {
        $cat = PostCategory::create(['slug' => 'herencias-y-sucesiones']);
        $undecided = $this->makePost(['slug' => 'cuanto-cuesta-sucesion-cdmx-2026', 'category_id' => $cat->id]);
        $decidedA = $this->makePost(['slug' => 'vender-propiedad-heredada-entre-hermanos', 'category_id' => $cat->id]);
        $decidedB = $this->makePost(['slug' => 'isr-venta-propiedad-heredada-mexico-2026', 'category_id' => $cat->id]);
        $decidedC = $this->makePost(['slug' => 'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx', 'category_id' => $cat->id]);

        $this->assertFalse(BlogCluster::showsSellCta($undecided));
        $this->assertTrue(BlogCluster::showsSellCta($decidedA));
        $this->assertTrue(BlogCluster::showsSellCta($decidedB));
        $this->assertTrue(BlogCluster::showsSellCta($decidedC));
    }

    public function test_manual_cluster_override_wins_over_heuristic(): void
    {
        $post = $this->makePost(['slug' => 'post-cualquiera', 'cluster' => BlogCluster::PROCESO_VENTA]);

        $this->assertSame(BlogCluster::PROCESO_VENTA, BlogCluster::forPost($post));
    }

    public function test_cta_capture_creates_a_lead_without_email_and_name_is_optional(): void
    {
        Mail::fake();
        $cat = PostCategory::create(['slug' => 'zonificacion-desarrollo']);
        $post = $this->makePost(['slug' => 'vender-casa-constructora-proceso-tiempos-cdmx', 'category_id' => $cat->id]);

        Livewire::test(\App\Livewire\Blog\CtaCapture::class, ['postId' => $post->id, 'location' => 'final'])
            ->set('whatsapp', '5511112222')
            ->set('aviso', true)
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertHasNoErrors();

        $lead = FormSubmission::where('phone', '5511112222')->first();
        $this->assertNotNull($lead);
        $this->assertNull($lead->email);
        $this->assertSame('vendedor_predio', $lead->form_type);
        $this->assertSame('Lead del blog', $lead->full_name);
    }

    /** Optimización post-lanzamiento: el correo sigue siendo opcional, pero si lo dan sí se guarda. */
    public function test_cta_capture_saves_optional_email_when_given(): void
    {
        Mail::fake();
        $post = $this->makePost(['slug' => 'post-con-correo-opcional']);

        Livewire::test(\App\Livewire\Blog\CtaCapture::class, ['postId' => $post->id, 'location' => 'inline'])
            ->set('whatsapp', '5511113333')
            ->set('email', 'prospecto@correo.com')
            ->set('aviso', true)
            ->call('submit');

        $lead = FormSubmission::where('phone', '5511113333')->first();
        $this->assertSame('prospecto@correo.com', $lead->email);
    }

    public function test_cta_capture_honeypot_blocks_silently(): void
    {
        Mail::fake();
        $post = $this->makePost(['slug' => 'post-honeypot']);

        Livewire::test(\App\Livewire\Blog\CtaCapture::class, ['postId' => $post->id, 'location' => 'inline'])
            ->set('whatsapp', '5599998888')
            ->set('aviso', true)
            ->set('website_url', 'soy un bot')
            ->call('submit');

        $this->assertDatabaseMissing('form_submissions', ['phone' => '5599998888']);
    }

    public function test_cta_capture_requires_whatsapp_but_not_name(): void
    {
        $post = $this->makePost(['slug' => 'post-sin-whatsapp']);

        Livewire::test(\App\Livewire\Blog\CtaCapture::class, ['postId' => $post->id])
            ->set('aviso', true)
            ->call('submit')
            ->assertHasErrors('whatsapp')
            ->assertHasNoErrors('name');
    }

    public function test_admin_cta_config_routes_exist_and_require_staff(): void
    {
        $this->assertTrue(Route::has('admin.blog-ctas.index'));
        $this->assertTrue(Route::has('admin.blog-ctas.update'));
        $this->assertContains('viewer', Route::getRoutes()->getByName('admin.blog-ctas.index')->gatherMiddleware());
    }
}
