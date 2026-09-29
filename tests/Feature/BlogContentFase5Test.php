<?php

namespace Tests\Feature;

use App\Support\BlogBodyEnhancer;
use Tests\Concerns\SetsUpMinimalBlogSchema;
use Tests\TestCase;

/** Fase 5 del prompt de leads del blog (docs/funcionalidades/blog-contenido-fase5.md). */
class BlogContentFase5Test extends TestCase
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
    }

    public function test_inject_before_first_heading_lands_before_the_first_h2(): void
    {
        $html = '<p>intro</p><h2>Primera sección</h2><p>x</p><h2>Segunda</h2><p>y</p>';
        $result = BlogBodyEnhancer::injectBeforeFirstHeading($html, '<div>RESPUESTA CORTA</div>');

        $this->assertLessThan(strpos($result, '<h2'), strpos($result, 'RESPUESTA CORTA'));
        $this->assertStringStartsWith('<p>intro</p><div>RESPUESTA CORTA</div><h2>', $result);
    }

    public function test_inject_before_first_heading_prepends_when_there_is_no_h2(): void
    {
        $html = '<p>sin encabezados</p>';
        $result = BlogBodyEnhancer::injectBeforeFirstHeading($html, '<div>X</div>');

        $this->assertStringStartsWith('<div>X</div>', $result);
    }

    public function test_succession_cost_summary_reads_live_config_and_shows_pending_notice(): void
    {
        $html = view('blog._succession-cost-summary')->render();

        $con = \App\Models\SuccessionCalculatorConfig::forScenario('con_testamento');
        $this->assertStringContainsString((string) $con->notarial_pct_min, $html);
        $this->assertStringContainsString('Respuesta corta', $html);
        $this->assertStringContainsString('todavía no confirmado con un notario', $html);   // seed sigue validated=false
    }

    public function test_succession_steps_summary_lists_between_three_and_five_steps(): void
    {
        $html = view('blog._succession-steps-summary')->render();

        $count = preg_match_all('/<li class="flex gap-3">/', $html);
        $this->assertGreaterThanOrEqual(3, $count);
        $this->assertLessThanOrEqual(5, $count);
        $this->assertStringContainsString('Confirma que no existe testamento', $html);
    }

    public function test_succession_summary_disappears_once_both_scenarios_are_validated(): void
    {
        \App\Models\SuccessionCalculatorConfig::query()->update(['validated' => true]);

        $html = view('blog._succession-cost-summary')->render();

        $this->assertStringNotContainsString('todavía no confirmado', $html);
    }

    public function test_meta_ctr_migration_never_exceeds_the_recommended_lengths(): void
    {
        $migration = require database_path('migrations/2026_09_30_150000_update_blog_meta_ctr_fase5.php');
        $constants = (new \ReflectionClass($migration))->getReflectionConstant('NEW')->getValue();

        foreach ($constants as $slug => [$title, $desc]) {
            $this->assertLessThanOrEqual(65, mb_strlen($title), "meta_title de {$slug} pasa de 65 caracteres");
            if ($desc !== null) {
                $this->assertGreaterThanOrEqual(140, mb_strlen($desc), "meta_description de {$slug} queda corta");
                $this->assertLessThanOrEqual(155, mb_strlen($desc), "meta_description de {$slug} pasa de 155 caracteres");
            }
        }
    }
}
