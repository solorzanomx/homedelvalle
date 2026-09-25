<?php

namespace Tests\Feature;

use App\Models\PolizaPlan;
use App\Support\PolizaPricing;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tarifas de la Hoja de Servicios AM QRO NL 2026 de Previsión Legal (las siembra la migración).
 * Ver docs/funcionalidades/garantia-y-poliza-inquilino.md
 */
class PolizaPricingTest extends TestCase
{
    /**
     * No usamos RefreshDatabase: corre TODAS las migraciones y una antigua ajena rompe en SQLite en memoria.
     * Aquí solo se migran las 3 de pólizas (sobre una tabla rental_processes mínima): siembran la hoja real.
     */
    protected function setUp(): void
    {
        parent::setUp();
        if (! Schema::hasTable('poliza_rates')) {
            Schema::create('rental_processes', fn($t) => $t->id());
            Artisan::call('migrate', ['--force' => true, '--path' => [
                'database/migrations/2026_09_26_100000_create_poliza_plans_and_tenant_guarantee_route.php',
                'database/migrations/2026_09_26_130000_poliza_plans_coverage_and_website_flags.php',
                'database/migrations/2026_09_27_100000_poliza_tariffs_coverage_and_owner_decision.php',
            ]]);
        }
    }

    private function price(string $plan, float $rent): ?float
    {
        return PolizaPricing::quote(PolizaPlan::where('name', $plan)->firstOrFail(), $rent)['amount'] ?? null;
    }

    public function test_every_range_boundary_matches_the_official_sheet(): void
    {
        // [renta, Básica, Superior, Integral] — cada tope pertenece a su rango ("hasta"); un centavo más cambia de rango.
        $cases = [
            [1, 3950, 5750, 7000], [7000, 3950, 5750, 7000],
            [7000.01, 4600, 6600, 8500], [10000, 4600, 6600, 8500],
            [10000.01, 5100, 7300, 9750], [15000, 5100, 7300, 9750],
            [15000.01, 5600, 8000, 11350], [20000, 5600, 8000, 11350],
            [20000.01, 5850, 8250, 13200], [25000, 5850, 8250, 13200],
            [25000.01, 6200, 8400, 15000], [30000, 6200, 8400, 15000],
        ];
        foreach ($cases as [$rent, $b, $s, $i]) {
            $this->assertEquals([$b, $s, $i], [$this->price('Básica', $rent), $this->price('Superior', $rent), $this->price('Integral', $rent)], "renta {$rent}");
        }
    }

    public function test_rents_above_30000_use_a_percentage_of_the_monthly_rent(): void
    {
        $this->assertEqualsWithDelta(6300.21, $this->price('Básica', 30001), 0.005);   // 21%
        $this->assertEqualsWithDelta(8850.30, $this->price('Superior', 30001), 0.006);  // 29.5%
        $this->assertEqualsWithDelta(15000.50, $this->price('Integral', 30001), 0.005); // 50%
        $this->assertEqualsWithDelta(50000, $this->price('Integral', 100000), 0.01);
        $this->assertSame('percent', PolizaPricing::quote(PolizaPlan::where('name', 'Integral')->first(), 45000)['basis']);
    }

    public function test_emission_fee_is_credited_to_the_price_and_no_quote_without_rent(): void
    {
        $q = PolizaPricing::quote(PolizaPlan::where('name', 'Superior')->first(), 15000);
        $this->assertSame(1700.0, $q['emission_fee']);
        $this->assertSame(7300 - 1700.0, $q['remaining_after_emission']);
        $this->assertNull(PolizaPricing::quote(PolizaPlan::where('name', 'Superior')->first(), 0));
    }

    public function test_split_is_all_tenant_or_half_and_half(): void
    {
        $this->assertSame(['tenant' => 7300.0, 'owner' => 0.0, 'tenant_pct' => 100, 'owner_pct' => 0], PolizaPricing::split(7300, 100));
        $this->assertSame(['tenant' => 3650.0, 'owner' => 3650.0, 'tenant_pct' => 50, 'owner_pct' => 50], PolizaPricing::split(7300, 50));
        $this->assertSame([100, 50], array_keys(PolizaPricing::SHARE_OPTIONS));
    }

    public function test_official_coverage_matrix_is_loaded(): void
    {
        $count = fn($name) => PolizaPlan::where('name', $name)->first()->load('coverages')->includedCoverages()->count();
        $this->assertSame([12, 15, 16], [$count('Básica'), $count('Superior'), $count('Integral')]);

        // Lo que la hoja marca con guion en cada plan
        $labels = fn($name) => PolizaPlan::where('name', $name)->first()->load('coverages')->includedCoverages()->pluck('label');
        $this->assertFalse($labels('Básica')->contains(fn($l) => str_contains($l, 'crediticios')));
        $this->assertTrue($labels('Superior')->contains(fn($l) => str_contains($l, 'vencimiento del contrato')));
        $this->assertFalse($labels('Superior')->contains(fn($l) => str_contains($l, '(pagarés)')));
        $this->assertTrue($labels('Integral')->contains(fn($l) => str_contains($l, '(pagarés)')));
    }
}
