<?php

namespace Tests\Feature;

use App\Models\BlogNotFoundHit;
use App\Models\FormSubmission;
use App\Models\PostCategory;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/** Fase 7 del prompt de leads del blog (docs/funcionalidades/blog-leads-panel.md). */
class BlogLeadsPanelTest extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
        foreach (['email_settings' => fn($t) => $t->id(), 'legal_documents' => function ($t) {
            $t->id(); $t->string('type')->nullable(); $t->string('status')->nullable(); $t->unsignedBigInteger('current_version_id')->nullable();
        }] as $table => $cb) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\Schema::create($table, $cb);
            }
        }
        if (! \Illuminate\Support\Facades\Schema::hasTable('form_submissions')) {
            \Illuminate\Support\Facades\Schema::create('form_submissions', function ($t) {
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
    }

    public function test_unresolved_404_gets_logged_and_counted(): void
    {
        $this->get('/blog/un-slug-que-nunca-existio-987654');
        $this->get('/blog/un-slug-que-nunca-existio-987654');

        $hit = BlogNotFoundHit::where('slug', 'un-slug-que-nunca-existio-987654')->first();
        $this->assertNotNull($hit);
        $this->assertSame(2, $hit->hits);
    }

    public function test_fuzzy_redirect_does_not_also_count_as_a_dead_end_404(): void
    {
        $post = $this->makePost(['slug' => 'cuanto-cuesta-sucesion-cdmx-2026']);

        $this->get('/blog/cuanto-cuesta-sucesion-cdmx-2027');   // suficientemente parecido, redirige solo

        $this->assertDatabaseMissing('blog_not_found_hits', ['slug' => 'cuanto-cuesta-sucesion-cdmx-2027']);
    }

    /**
     * El admin completo (layouts.app-sidebar) consulta demasiadas tablas ajenas al blog para
     * renderizarlo en este esquema mínimo (mismo criterio que las fases anteriores) — se prueba la
     * lógica de agregación del controlador directamente, sin pasar por el layout del CRM. El
     * render completo (con datos reales) ya se verificó a mano contra la BD local.
     */
    public function test_panel_groups_leads_by_post_cluster_and_cta_variant(): void
    {
        Mail::fake();
        $cat = PostCategory::create(['slug' => 'herencias-y-sucesiones']);
        $post = $this->makePost(['slug' => 'post-con-leads', 'category_id' => $cat->id, 'views_count' => 100]);

        FormSubmission::create([
            'form_type' => 'vendedor_predio', 'full_name' => 'Lead 1', 'phone' => '5511110000',
            'landing_post_id' => $post->id, 'landing_label' => 'Blog: x',
            'payload' => ['origen' => 'blog_cta', 'cluster' => 'herencias', 'cta_variant' => 'decidido'],
        ]);
        FormSubmission::create([
            'form_type' => 'contacto', 'full_name' => 'Lead 2', 'phone' => '5511110001',
            'landing_post_id' => $post->id, 'landing_label' => 'Blog: x', 'payload' => [],
        ]);

        $view = (new \App\Http\Controllers\Admin\BlogLeadsController())->index(request());
        $data = $view->getData();

        $this->assertSame(2, $data['totalLeads']);
        $byPost = $data['byPost']->firstWhere('slug', $post->slug);
        $this->assertNotNull($byPost);
        $this->assertSame(2, $byPost['conversions']);
        $this->assertSame(2.0, $byPost['rate']);   // 2 leads / 100 vistas

        $this->assertSame(2, $data['byCluster']->firstWhere('cluster', 'herencias')['count']);
        $this->assertSame(1, $data['byCtaVariant']->firstWhere('variant', 'decidido')['count']);
    }

    public function test_route_requires_staff(): void
    {
        $this->assertTrue(Route::has('admin.blog-leads.index'));
        $this->assertContains('viewer', Route::getRoutes()->getByName('admin.blog-leads.index')->gatherMiddleware());
    }
}
