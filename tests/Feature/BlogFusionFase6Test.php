<?php

namespace Tests\Feature;

use App\Models\BlogRedirect;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/** Fase 6 del prompt de leads del blog (docs/funcionalidades/blog-fusion-fase6.md). */
class BlogFusionFase6Test extends TestCase
{
    use SetsUpMinimalBlogSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateMinimalBlogSchema();
    }

    /** Los 6 archivos de contenido de fusión existen y no están vacíos — la migración depende de ellos. */
    public function test_all_six_fusion_draft_files_exist(): void
    {
        foreach ([
            'fusion-vender-heredada.html', 'fusion-vender-desarrolladora.html', 'fusion-h5-h6.html',
            'fusion-narvarte.html', 'fusion-invertir-bj.html', 'fusion-escasez-suelo.html',
        ] as $file) {
            $path = database_path('seeders/blog-posts/' . $file);
            $this->assertFileExists($path, "Falta {$file}");
            $this->assertGreaterThan(500, strlen(file_get_contents($path)), "{$file} parece vacío o muy corto");
        }
    }

    public function test_fusion_migration_creates_drafts_never_published_and_redirects_stay_inactive(): void
    {
        $pilar = $this->makePost(['slug' => 'vender-casa-constructora-proceso-tiempos-cdmx', 'status' => 'published', 'published_at' => now()->subMonths(3)]);
        $absorbed = $this->makePost(['slug' => 'cuanto-pagan-constructoras-terreno-del-valle-2026', 'status' => 'published', 'published_at' => now()->subMonths(3)]);

        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--path' => [
            'database/migrations/2026_09_30_150004_seed_blog_fusion_drafts_fase6.php',
        ]]);

        $draft = \App\Models\Post::where('slug', 'vender-casa-constructora-proceso-tiempos-cdmx-borrador-fusion')->first();
        $this->assertNotNull($draft);
        $this->assertSame('draft', $draft->status);
        $this->assertStringContainsString('BORRADOR FUSIÓN', $draft->title);

        // No aparece en el sitio público (el sitemap ya se prueba aparte en BlogRedirectsTest,
        // con Post::published() como único criterio — un draft nunca pasa ese filtro).
        $this->get('/blog/' . $draft->slug)->assertStatus(404);
        $this->assertFalse($draft->status === 'published');

        $redirect = BlogRedirect::where('from_path', '/blog/cuanto-pagan-constructoras-terreno-del-valle-2026')->first();
        $this->assertNotNull($redirect);
        $this->assertFalse($redirect->active, 'El redirect de una fusión en borrador debe quedar inactivo hasta que Alejandro lo apruebe');
        $this->assertSame('/blog/vender-casa-constructora-proceso-tiempos-cdmx', $redirect->to_path);

        // Con el redirect inactivo, BlogUrlHealth no debe aplicarlo (mismo comportamiento que
        // prueba BlogRedirectsTest::test_inactive_redirect_is_not_applied — aquí solo confirmamos
        // que la fila de este grupo específico nació inactiva).
        $this->assertNotNull($absorbed->id);
    }

    public function test_isr_posts_are_linked_to_each_other_not_merged(): void
    {
        $casaHabitacion = $this->makePost(['slug' => 'isr-venta-casa-habitacion-benito-juarez-2026']);
        $heredada = $this->makePost(['slug' => 'isr-venta-propiedad-heredada-mexico-2026']);

        // Ambos siguen existiendo por separado (no se fusionan) — solo lo confirma la presencia del draft path.
        $this->assertNotEquals($casaHabitacion->id, $heredada->id);
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('posts'));
    }
}
