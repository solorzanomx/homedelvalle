<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Support\ExpedienteFields;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Protege el obligado solidario (docs/funcionalidades/obligado-solidario.md). Sin base de datos. */
class ObligadoSolidarioTest extends TestCase
{
    public function test_questionnaire_keeps_references_employer_and_previous_landlord(): void
    {
        $fillable = (new Client())->getFillable();
        foreach (array_merge(ExpedienteFields::PERSONAL, ExpedienteFields::IDENTIFICATION, ExpedienteFields::INCOME_TENANT, ExpedienteFields::INCOME_OBLIGADO) as $f) {
            $this->assertContains($f, $fillable, "Client no tiene el campo '{$f}'");
        }
        // Mismo cuestionario que el inquilino (menos "hogar"): trabajo, antiguo arrendador y 3 referencias.
        foreach (['employer_name', 'employer_phone', 'job_seniority', 'previous_landlord_name', 'previous_landlord_phone'] as $f) {
            $this->assertContains($f, ExpedienteFields::INCOME_OBLIGADO, "El obligado debe conservar '{$f}'");
        }
        $this->assertSame(3, ExpedienteFields::REFERENCES_REQUIRED);
    }

    public function test_obligado_has_no_account_and_only_the_tenant_can_act_for_them(): void
    {
        $src = file_get_contents(app_path('Services/ObligadoSolidarioService.php'));
        $this->assertStringNotContainsString('createPortalAccount', $src, 'el obligado NO tiene cuenta/Portal');
        $this->assertStringContainsString('function tenantMayActFor', $src);

        // Todo punto que actúa "a nombre del obligado" pasa por esa única puerta.
        foreach (['Http/Controllers/Portal/PortalExpedienteController.php', 'Http/Controllers/Portal/PortalDocumentController.php', 'Livewire/Portal/DocumentUploader.php'] as $f) {
            $this->assertStringContainsString('tenantMayActFor', file_get_contents(app_path($f)), "{$f} debe autorizar con tenantMayActFor");
        }
    }

    public function test_routes_exist_and_crm_ones_require_staff(): void
    {
        foreach (['portal.rentals.obligado.store', 'rentals.obligado.register', 'rentals.obligado.toggle', 'portal.expediente', 'portal.documents.index'] as $name) {
            $this->assertTrue(Route::has($name), "Falta la ruta {$name}");
        }
        $this->assertFalse(Route::has('portal.rentals.obligado.resend'), 'ya no hay invitaciones: el obligado no tiene cuenta');
        $this->assertContains('viewer', Route::getRoutes()->getByName('rentals.obligado.register')->gatherMiddleware());
    }

    public function test_rejected_references_do_not_count_and_have_crm_routes(): void
    {
        foreach (['rentals.references.reject', 'rentals.references.restore', 'rentals.references.save'] as $name) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($name), "Falta la ruta {$name}");
            $this->assertContains('viewer', \Illuminate\Support\Facades\Route::getRoutes()->getByName($name)->gatherMiddleware());
        }
        $ref = new \App\Models\ClientReference(['status' => 'rejected']);
        $this->assertTrue($ref->isRejected());
        $this->assertFalse((new \App\Models\ClientReference(['status' => 'pending']))->isRejected());
        // Toda cuenta de "3 referencias" debe excluir las rechazadas.
        $this->assertStringContainsString('references()->valid()', file_get_contents(app_path('Services/ObligadoSolidarioService.php')));
        $this->assertStringContainsString('isRejected()', file_get_contents(app_path('Http/Controllers/Portal/PortalExpedienteController.php')));
    }
}
