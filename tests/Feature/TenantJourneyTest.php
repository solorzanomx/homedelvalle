<?php

namespace Tests\Feature;

use App\Models\RentalProcess;
use App\Support\TenantRoadmap;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Protege "Mi camino" y la navegación del inquilino (docs/funcionalidades/portal-inquilino-navegacion.md). Sin base de datos. */
class TenantJourneyTest extends TestCase
{
    private function steps(string $current, array $override = []): array
    {
        $base = [
            'apartado' => ['key' => 'apartado', 'done' => true],
            'informacion' => ['key' => 'informacion', 'done' => false],
            'documentos' => ['key' => 'documentos', 'done' => false, 'counts' => ['approved' => 0, 'review' => 0, 'rejected' => 0], 'missing' => ['Identificación']],
            'garantia' => ['key' => 'garantia', 'done' => false, 'summary' => 'x', 'route' => 'poliza', 'action' => 'choose_plan'],
            'contrato' => ['key' => 'contrato', 'done' => false, 'summary' => 'y', 'contract' => null],
            'entrega' => ['key' => 'entrega', 'done' => false, 'summary' => 'z'],
        ];

        return ['route' => 'poliza', 'steps' => array_values(array_replace_recursive($base, $override)), 'plans' => collect(), 'current' => $current];
    }

    public function test_every_step_gives_one_concrete_next_action(): void
    {
        $r = new RentalProcess();

        $a = TenantRoadmap::nextAction($r, $this->steps('informacion'));
        $this->assertSame('Completa tus datos', $a['title']);
        $this->assertNotNull($a['cta_url']);

        $a = TenantRoadmap::nextAction($r, $this->steps('documentos'));
        $this->assertStringContainsString('Sube tu', $a['title']);

        $a = TenantRoadmap::nextAction($r, $this->steps('documentos', ['documentos' => ['counts' => ['rejected' => 2]]]));
        $this->assertStringContainsString('Corrige 2 documentos', $a['title']);

        $a = TenantRoadmap::nextAction($r, $this->steps('garantia'));
        $this->assertSame('Elige tu plan de póliza', $a['title']);
        $this->assertSame('#step-garantia', $a['cta_url'], 'el plan se elige dentro del camino');

        $a = TenantRoadmap::nextAction($r, $this->steps('contrato'));
        $this->assertNull($a['cta_url'], 'sin contrato disponible no hay nada que hacer: solo esperar');
    }

    public function test_pending_apartado_is_a_secondary_reminder_not_the_main_step(): void
    {
        $steps = $this->steps('informacion', ['apartado' => ['done' => false]]);
        $a = TenantRoadmap::nextAction(new RentalProcess(), $steps);

        $this->assertSame('Completa tus datos', $a['title']);
        $this->assertSame('Aparta tu inmueble', $a['secondary']['title']);
    }

    public function test_tenant_navigation_routes_exist_and_expediente_is_no_longer_a_menu_destination(): void
    {
        foreach (['portal.journey', 'portal.documents.index', 'portal.rentals.show', 'portal.expediente'] as $name) {
            $this->assertTrue(Route::has($name), "Falta la ruta {$name}");
        }
        $layout = file_get_contents(resource_path('views/layouts/portal.blade.php'));
        $this->assertStringContainsString('$tenantNav', $layout);
        $this->assertStringContainsString('portal._tenant_bottom_nav', $layout);
    }

    public function test_tenant_mode_does_not_depend_on_interest_types_and_topbar_uses_the_dark_logo(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/portal.blade.php'));

        // Misma fuente de verdad que Mi camino: la renta activa como inquilino, no el interés capturado.
        $this->assertStringContainsString('activeTenantRental($portalClient)', $layout);

        // La barra superior es de fondo oscuro: el logo para fondo oscuro va primero.
        $top = substr($layout, strpos($layout, 'class="portal-topbar"'), 1200);
        $this->assertLessThan(strpos($top, 'logo_path ??'), strpos($top, 'logo_path_dark'), 'el logo oscuro debe evaluarse antes que el claro');
    }

    private function docs(array $specs): \Illuminate\Support\Collection
    {
        return collect($specs)->map(fn($s) => new \App\Models\Document(['category' => $s[0], 'status' => $s[1] ?? 'received', 'rejection_reason' => $s[2] ?? null]));
    }

    public function test_identification_is_one_or_the_other(): void
    {
        $rows = \App\Support\TenantDocumentRows::class;

        $this->assertNull($rows::idMode($this->docs([])), 'sin nada subido: primero elige cuál usará');
        $this->assertSame('ine', $rows::idMode($this->docs([['ine_frente']])), 'con INE subida ya no se ofrece pasaporte');
        $this->assertSame('ine', $rows::idMode($this->docs([['ine_reverso', 'rejected']])));
        $this->assertSame('pasaporte', $rows::idMode($this->docs([['pasaporte']])));
        $this->assertSame('ine', $rows::idMode($this->docs([]), 'ine_frente'), 'al tocar INE en el selector se abre la carga de INE');
        $this->assertSame('pasaporte', $rows::idMode($this->docs([]), 'pasaporte'));
    }

    public function test_income_needs_the_last_three_files_not_just_one(): void
    {
        $rows = \App\Support\TenantDocumentRows::class;

        $this->assertSame('falta', $rows::stateFor($this->docs([]), 3)[0]);
        [$state, $uploaded] = $rows::stateFor($this->docs([['estado_cuenta']]), 3);
        $this->assertSame(['parcial', 1], [$state, $uploaded], 'con 1 de 3 sigue incompleto');
        $this->assertSame('revision', $rows::stateFor($this->docs([['estado_cuenta'], ['estado_cuenta'], ['estado_cuenta']]), 3)[0]);
        $this->assertSame('aprobado', $rows::stateFor($this->docs([['nomina', 'verified'], ['nomina', 'verified'], ['nomina', 'verified']]), 3)[0]);

        [$state, , $reason] = $rows::stateFor($this->docs([['estado_cuenta', 'rejected', 'Foto de pantalla']]), 3);
        $this->assertSame(['corregir', 'Foto de pantalla'], [$state, $reason]);

        $this->assertSame(3, \App\Support\TenantDocumentRows::INCOME_MONTHS);
        $this->assertSame(3, \App\Support\RentalExpedienteStatus::INCOME_MONTHS);
    }

    private function wizard(array $sections, array $rentalAttrs, string $requested = ''): array
    {
        $m = new \ReflectionMethod(\App\Http\Controllers\Portal\PortalExpedienteController::class, 'wizardSteps');
        $m->setAccessible(true);

        return $m->invoke(app(\App\Http\Controllers\Portal\PortalExpedienteController::class), $sections, new RentalProcess($rentalAttrs), $requested);
    }

    public function test_wizard_steps_skip_aval_with_poliza_and_open_on_the_first_incomplete_step(): void
    {
        $sec = fn($p) => ['pct' => $p];
        $sections = ['datos' => $sec(100), 'identificacion' => $sec(40), 'hogar' => $sec(0), 'referencias' => $sec(0), 'ingresos' => $sec(0), 'garantia' => $sec(0)];

        $poliza = $this->wizard($sections, ['tenant_has_aval' => false]);
        $this->assertSame(['datos', 'identificacion', 'hogar', 'referencias', 'ingresos'], array_keys($poliza['steps']), 'con póliza no hay paso de aval');
        $this->assertSame('identificacion', $poliza['current'], 'abre en el primer paso incompleto');
        $this->assertSame(['datos', 'hogar'], [$poliza['prev'], $poliza['next']]);
        $this->assertSame(2, $poliza['index']);

        $aval = $this->wizard($sections, ['tenant_has_aval' => true]);
        $this->assertArrayHasKey('garantia', $aval['steps']);
        $this->assertSame('fin', $this->wizard($sections, ['tenant_has_aval' => true], 'garantia')['next'], 'el último paso termina en Mi camino');
        $this->assertSame('identificacion', $this->wizard($sections, ['tenant_has_aval' => false], 'inventado')['current'], 'un paso inválido cae al primero incompleto');
    }
}
