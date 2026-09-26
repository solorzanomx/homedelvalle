<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Support\ExpedienteFields;
use App\Support\ObligadoRoadmap;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Protege el obligado solidario (docs/funcionalidades/obligado-solidario.md). Sin base de datos. */
class ObligadoSolidarioTest extends TestCase
{
    public function test_field_lists_exist_on_the_client_and_obligado_has_no_tenant_only_fields(): void
    {
        $fillable = (new Client())->getFillable();
        foreach (array_merge(ExpedienteFields::PERSONAL, ExpedienteFields::IDENTIFICATION, ExpedienteFields::INCOME_TENANT, ExpedienteFields::INCOME_OBLIGADO) as $f) {
            $this->assertContains($f, $fillable, "Client no tiene el campo '{$f}'");
        }
        // El obligado no aporta "arrendador anterior" ni el tipo de comprobante (sus documentos van en Mis documentos).
        $this->assertNotContains('previous_landlord_name', ExpedienteFields::INCOME_OBLIGADO);
        $this->assertNotContains('income_proof_type', ExpedienteFields::INCOME_OBLIGADO);
    }

    public function test_service_never_uses_the_account_creator_that_resets_existing_users(): void
    {
        // createPortalAccount reutiliza al usuario con ese correo y le cambia rol y contraseña: NO debe usarse aquí.
        $src = file_get_contents(app_path('Services/ObligadoSolidarioService.php'));
        $this->assertStringNotContainsString('->createPortalAccount(', $src);
        $this->assertStringContainsString("\$user->role !== 'client'", $src, 'debe rechazar correos de cuentas internas');
    }

    public function test_obligado_next_action_is_always_one_concrete_step(): void
    {
        $rm = fn(string $current, array $over = []) => ['route' => null, 'plans' => collect(), 'current' => $current, 'steps' => [
            array_replace(['key' => 'informacion', 'done' => false], $over['informacion'] ?? []),
            array_replace(['key' => 'documentos', 'done' => false, 'counts' => ['corregir' => 0, 'falta' => 4]], $over['documentos'] ?? []),
            ['key' => 'revision', 'done' => false],
        ]];

        $this->assertSame('Completa tus datos', ObligadoRoadmap::nextAction($rm('informacion'))['title']);
        $this->assertSame('Sube tus documentos', ObligadoRoadmap::nextAction($rm('documentos'))['title']);
        $this->assertStringContainsString('Corrige 2', ObligadoRoadmap::nextAction($rm('documentos', ['documentos' => ['counts' => ['corregir' => 2, 'falta' => 0]]]))['title']);
        $this->assertNull(ObligadoRoadmap::nextAction($rm('revision'))['cta_url'], 'en revisión solo se espera');
    }

    public function test_routes_and_privacy_wiring_exist(): void
    {
        foreach (['portal.rentals.obligado.store', 'portal.rentals.obligado.resend', 'rentals.obligado.register', 'rentals.obligado.resend', 'rentals.obligado.toggle', 'portal.journey'] as $name) {
            $this->assertTrue(Route::has($name), "Falta la ruta {$name}");
        }
        // Las rutas del CRM del obligado exigen personal (el SmokeTest ya lo vigila globalmente).
        $this->assertContains('viewer', Route::getRoutes()->getByName('rentals.obligado.register')->gatherMiddleware());

        // El inquilino solo ve nombre y avance del obligado: nunca su correo ni datos.
        $roadmap = file_get_contents(resource_path('views/portal/_tenant_roadmap.blade.php'));
        $this->assertStringContainsString('Por privacidad no ves sus datos ni sus documentos', $roadmap);
        $this->assertStringNotContainsString("os['client']->email", $roadmap);
    }
}
