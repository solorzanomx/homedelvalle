<?php

namespace Tests\Feature;

use App\Models\BlogRedirect;
use App\Models\Post;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Fase 1 del prompt de leads del blog (docs/funcionalidades/blog-redirects.md).
 * No usamos RefreshDatabase (corre TODAS las migraciones y una antigua ajena — MySQL-only —
 * rompe en SQLite en memoria, mismo gotcha de PolizaPricingTest). Solo migramos lo que este
 * módulo toca: posts, post_categories (mínimas, tal cual sus migraciones reales) y blog_redirects.
 */
class BlogRedirectsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! Schema::hasTable('blog_redirects')) {
            Schema::create('post_categories', fn(Blueprint $t) => $t->id());
            Schema::create('posts', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->string('title');
                $t->string('slug')->unique();
                $t->text('excerpt')->nullable();
                $t->longText('body');
                $t->unsignedBigInteger('category_id')->nullable();
                $t->string('status')->default('draft');
                $t->timestamp('published_at')->nullable();
                $t->timestamps();
            });
            // blog.not-found/blog.gone extienden layouts.public → el footer necesita que existan
            // 'pages' y 'menus'/'menu_items' (AppServiceProvider las consulta con try/catch, pero el
            // fallback a collect() cuando NO existen rompe $footerMenu->items — bug real, ajeno a este
            // módulo). Vacías, sin migrar toda la cadena de 'pages' (tiene una MySQL-only más adelante).
            Schema::create('pages', function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('slug')->unique();
                $t->longText('body')->nullable();
                $t->boolean('is_published')->default(false);
                $t->boolean('show_in_nav')->default(false);
                $t->unsignedInteger('nav_order')->default(0);
                $t->string('nav_label')->nullable();
                $t->string('nav_url')->nullable();
                $t->string('nav_route')->nullable();
                $t->string('nav_style')->nullable();
                $t->timestamps();
            });
            Schema::create('menus', fn(Blueprint $t) => $t->id());
            Schema::create('menu_items', fn(Blueprint $t) => $t->id());

            Artisan::call('migrate', ['--force' => true, '--path' => [
                'database/migrations/2026_03_29_135219_create_site_settings_table.php',
                'database/migrations/2026_09_28_100000_create_blog_redirects_table.php',
            ]]);
        }
    }

    private function publishedPost(string $slug): Post
    {
        return Post::create([
            'user_id' => 1, 'title' => 'Título de prueba', 'slug' => $slug, 'body' => '<p>contenido</p>',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
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
