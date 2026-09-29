<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema mínimo para tests del blog que rinden vistas públicas completas, sobre SQLite en memoria.
 * No usamos RefreshDatabase (corre TODAS las migraciones y una antigua ajena con sintaxis MySQL-only
 * rompe el `migrate` completo ahí — mismo gotcha de PolizaPricingTest). Reusado por BlogRedirectsTest
 * y BlogGa4TrackingTest — cualquier fase nueva que necesite renderizar `blog/show` o `layouts.public`
 * en un test debería usar este mismo trait en vez de duplicar las tablas a mano.
 */
trait SetsUpMinimalBlogSchema
{
    protected function migrateMinimalBlogSchema(): void
    {
        if (Schema::hasTable('posts')) {
            return;
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('avatar_path')->nullable();
            $t->string('title')->nullable();
            $t->text('bio')->nullable();
            $t->timestamps();
        });
        Schema::create('post_categories', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->nullable();
            $t->timestamps();
        });
        // blog/show carga tags() (belongsToMany) — vacías, ningún post de prueba les asigna tags.
        Schema::create('tags', fn(Blueprint $t) => $t->id());
        Schema::create('post_tag', function (Blueprint $t) {
            $t->unsignedBigInteger('post_id');
            $t->unsignedBigInteger('tag_id');
        });
        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('excerpt')->nullable();
            $t->longText('body');
            $t->string('featured_image')->nullable();
            $t->unsignedBigInteger('category_id')->nullable();
            $t->string('cluster', 40)->nullable();
            $t->string('status')->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->json('faq_schema')->nullable();
            $t->unsignedInteger('views_count')->default(0);
            $t->timestamps();
        });

        // blog/show y blog/not-found extienden layouts.public → el footer necesita que existan
        // 'pages' y 'menus'/'menu_items' (AppServiceProvider las consulta con try/catch, pero el
        // fallback a collect() cuando NO existen rompe $footerMenu->items — bug real, ajeno a estos
        // módulos). Vacías, sin migrar toda la cadena de 'pages' (tiene una MySQL-only más adelante).
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
            'database/migrations/2026_09_30_160000_create_blog_not_found_hits_table.php',
        ]]);

        \Illuminate\Support\Facades\DB::table('users')->insert(['id' => 1, 'name' => 'Autor de prueba', 'created_at' => now(), 'updated_at' => now()]);
    }

    protected function makePost(array $attrs = []): \App\Models\Post
    {
        return \App\Models\Post::create($attrs + [
            'user_id' => 1, 'title' => 'Título de prueba', 'slug' => 'post-de-prueba-' . uniqid(),
            'body' => '<p>contenido</p><h2>Un encabezado</h2>', 'status' => 'published', 'published_at' => now()->subDay(),
        ]);
    }
}
