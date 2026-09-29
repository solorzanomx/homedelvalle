<?php

namespace Tests\Feature;

use App\Models\FormSubmission;
use App\Models\PostCategory;
use App\Support\BlogCluster;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/**
 * Fase 2 del prompt de leads del blog (docs/funcionalidades/blog-ga4-tracking.md).
 * hdvTrack corre en el navegador — aquí solo verificamos lo que el servidor le entrega al JS
 * (el contexto post_slug/post_cluster inyectado) y la lógica PHP de BlogCluster/FormSubmission.
 */
class BlogGa4TrackingTest extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
    }

    /**
     * blog/show extiende layouts.public, que jala colonias/menús/redes/testimonios reales —
     * reproducirlo entero en SQLite en memoria es más frágil que útil (mismo criterio que la nota
     * de memoria del proyecto: verificar el render del blog se hace contra la BD local real, con
     * `php artisan serve` y un post sintético, no en PHPUnit). Aquí verificamos la FUENTE exacta
     * que expone el contexto — ya probado renderizando de verdad vía tinker en esta sesión.
     */
    public function test_blog_show_view_exposes_slug_and_cluster_for_ga4(): void
    {
        $view = file_get_contents(resource_path('views/blog/show.blade.php'));

        $this->assertStringContainsString('window.hdvBlogContext', $view);
        $this->assertStringContainsString('post_slug: @json($post->slug)', $view);
        $this->assertStringContainsString('post_cluster: @json(\App\Support\BlogCluster::forPost($post))', $view);
    }

    public function test_cluster_by_slug_wins_over_category_for_herencias(): void
    {
        $cat = PostCategory::create(['slug' => 'colonias-de-benito-juarez']);
        $post = $this->makePost(['slug' => 'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx', 'category_id' => $cat->id]);

        $this->assertSame(BlogCluster::HERENCIAS, BlogCluster::forPost($post));
    }

    public function test_cluster_maps_from_category_when_slug_has_no_hint(): void
    {
        $cat = PostCategory::create(['slug' => 'zonificacion-desarrollo']);
        $post = $this->makePost(['slug' => 'vender-casa-constructora-proceso-tiempos-cdmx', 'category_id' => $cat->id]);

        $this->assertSame(BlogCluster::TERRENO_DESARROLLADORA, BlogCluster::forPost($post));
    }

    public function test_uncategorized_post_has_no_cluster(): void
    {
        $post = $this->makePost(['slug' => 'post-sin-categoria']);

        $this->assertNull(BlogCluster::forPost($post));
        $this->assertNull(BlogCluster::forPost(null));
    }

    public function test_form_submission_exposes_post_slug_and_cluster_from_its_landing_post(): void
    {
        $cat = PostCategory::create(['slug' => 'herencias-y-sucesiones']);
        $post = $this->makePost(['slug' => 'isr-venta-propiedad-heredada-mexico-2026', 'category_id' => $cat->id]);

        $submission = new FormSubmission(['form_type' => 'vendedor']);
        $submission->setRelation('landingPost', $post);

        $this->assertSame('isr-venta-propiedad-heredada-mexico-2026', $submission->post_slug);
        $this->assertSame(BlogCluster::HERENCIAS, $submission->post_cluster);
    }

    public function test_public_layout_merges_blog_context_and_tracks_cta_variant_and_cta_view(): void
    {
        $js = file_get_contents(resource_path('views/layouts/public.blade.php'));

        $this->assertStringContainsString('window.hdvBlogContext', $js);
        $this->assertStringContainsString('cta_variant', $js);
        $this->assertStringContainsString("hdvTrack('cta_view'", $js);
        $this->assertStringContainsString('IntersectionObserver', $js);
    }
}
