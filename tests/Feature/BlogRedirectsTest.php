<?php

namespace Tests\Feature;

use App\Models\BlogRedirect;
use App\Models\Post;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/**
 * Fase 1 del prompt de leads del blog (docs/funcionalidades/blog-redirects.md).
 * No usamos RefreshDatabase (corre TODAS las migraciones y una antigua ajena — MySQL-only —
 * rompe en SQLite en memoria, mismo gotcha de PolizaPricingTest). Ver SetsUpMinimalBlogSchema.
 */
class BlogRedirectsTest extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
    }

    private function publishedPost(string $slug): Post
    {
        return $this->makePost(['slug' => $slug]);
    }

    public function test_seeded_redirect_sends_301_and_counts_hits(): void
    {
        $target = $this->publishedPost('cuanto-cuesta-sucesion-cdmx-2026');
        $r = BlogRedirect::create(['from_path' => '/blog/cuanto-sucesion-cdmx-2026', 'to_path' => '/blog/' . $target->slug, 'status' => 301, 'active' => true]);

        $this->get('/blog/cuanto-sucesion-cdmx-2026')->assertRedirect('/blog/' . $target->slug);
        $this->assertSame(1, $r->fresh()->hits);
        $this->assertNotNull($r->fresh()->last_hit_at);
    }

    public function test_uppercase_and_trailing_slash_normalize_to_301(): void
    {
        $this->publishedPost('un-post-cualquiera');

        $this->get('/blog/UN-POST-CUALQUIERA/')->assertRedirect('/blog/un-post-cualquiera');
    }

    public function test_inactive_redirect_is_not_applied(): void
    {
        $this->publishedPost('destino-real');
        BlogRedirect::create(['from_path' => '/blog/viejo', 'to_path' => '/blog/destino-real', 'status' => 301, 'active' => false]);

        $this->get('/blog/viejo')->assertStatus(404);
    }

    public function test_410_redirect_renders_gone(): void
    {
        BlogRedirect::create(['from_path' => '/blog/retirado', 'to_path' => '/blog', 'status' => 410, 'active' => true]);

        $this->get('/blog/retirado')->assertStatus(410);
    }

    public function test_close_typo_falls_back_to_closest_published_post_and_registers_auto_redirect(): void
    {
        $post = $this->publishedPost('cuanto-cuesta-sucesion-cdmx-2026');

        $this->get('/blog/cuanto-cuesta-sucesion-cdmx-2027')->assertRedirect('/blog/' . $post->slug);
        $this->assertDatabaseHas('blog_redirects', ['from_path' => '/blog/cuanto-cuesta-sucesion-cdmx-2027', 'to_path' => '/blog/' . $post->slug]);
    }

    public function test_unrelated_slug_gets_a_useful_404_not_a_forced_redirect(): void
    {
        $this->publishedPost('precio-metro-cuadrado-colonias-benito-juarez-2026');

        $response = $this->get('/blog/esto-no-tiene-nada-que-ver-987');
        $response->assertStatus(404);
        $response->assertSee('No encontramos ese artículo');
        $this->assertDatabaseMissing('blog_redirects', ['from_path' => '/blog/esto-no-tiene-nada-que-ver-987']);
    }

    public function test_changing_a_post_slug_auto_creates_a_redirect_from_the_old_one(): void
    {
        $post = $this->publishedPost('slug-original-2026');
        $post->update(['slug' => 'slug-nuevo-2026']);

        $this->assertDatabaseHas('blog_redirects', ['from_path' => '/blog/slug-original-2026', 'to_path' => '/blog/slug-nuevo-2026']);
        $this->get('/blog/slug-original-2026')->assertRedirect('/blog/slug-nuevo-2026');
    }

    public function test_admin_crud_routes_exist_and_require_staff(): void
    {
        foreach (['admin.blog-redirects.index', 'admin.blog-redirects.store', 'admin.blog-redirects.toggle', 'admin.blog-redirects.destroy'] as $name) {
            $this->assertTrue(Route::has($name), "Falta la ruta {$name}");
        }
        $this->assertContains('viewer', Route::getRoutes()->getByName('admin.blog-redirects.index')->gatherMiddleware());
    }
}
