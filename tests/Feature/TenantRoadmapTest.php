<?php

namespace Tests\Feature;

use App\Models\PolizaPlan;
use App\Models\RentalProcess;
use App\Support\TenantRoadmap;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Protege la ruta de garantía del inquilino (docs/funcionalidades/garantia-y-poliza-inquilino.md). Sin base de datos. */
class TenantRoadmapTest extends TestCase
{
    public function test_no_aval_in_cdmx_is_always_poliza(): void
    {
        // Aunque el asesor tuviera aval/pagarés puesto, la declaración "no tengo aval" manda: es póliza.
        foreach (['deposito', 'aval', 'aval_pagares', 'poliza_juridica', 'fianza'] as $type) {
            $r = new RentalProcess(['guarantee_type' => $type, 'tenant_has_aval' => false]);
            $this->assertSame(TenantRoadmap::ROUTE_POLIZA, TenantRoadmap::route($r), "guarantee_type={$type}");
        }
    }

    public function test_declared_aval_goes_to_investigation_and_default_deposit_is_undecided(): void
    {
        $this->assertSame(TenantRoadmap::ROUTE_AVAL, TenantRoadmap::route(new RentalProcess(['tenant_has_aval' => true, 'guarantee_type' => 'deposito'])));
        $this->assertSame(TenantRoadmap::ROUTE_AVAL, TenantRoadmap::route(new RentalProcess(['guarantee_type' => 'aval_pagares'])));
        $this->assertSame(TenantRoadmap::ROUTE_POLIZA, TenantRoadmap::route(new RentalProcess(['guarantee_type' => 'poliza_juridica'])));
        // 'deposito' es el default de la columna: no dice nada sobre lo que el inquilino tiene.
        $this->assertSame(TenantRoadmap::ROUTE_UNDECIDED, TenantRoadmap::route(new RentalProcess(['guarantee_type' => 'deposito'])));
        $this->assertSame(3500, TenantRoadmap::INVESTIGATION_FEE);
    }

    public function test_plan_price_formatting_and_routes(): void
    {
        $this->assertSame('$6,000 MXN', (new PolizaPlan(['price' => 6000, 'currency' => 'MXN']))->price_formatted);
        $this->assertSame('Precio por confirmar', (new PolizaPlan(['price' => null]))->price_formatted);
        foreach (['poliza-plans.index', 'rentals.guarantee-route', 'rentals.contracts.auto-generate', 'portal.rentals.guarantee.declare', 'portal.rentals.poliza.select'] as $name) {
            $this->assertTrue(Route::has($name), "Falta la ruta {$name}");
        }
    }
}
