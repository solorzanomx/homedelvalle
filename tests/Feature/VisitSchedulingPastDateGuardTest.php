<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Incidente real 2026-10-01: Yarlin (lead que ya había visitado un depa y dejó feedback) recibió
 * un correo de "tu visita está agendada" para una fecha ya pasada (26 de septiembre), porque Ana
 * Laura reabrió el formulario de "Agendar visita" sobre el lead viejo y ninguna de las 4 pantallas
 * desde donde se puede agendar una visita validaba que la fecha fuera hoy o futura — todas mandan
 * el correo de confirmación de inmediato salvo que se desmarque la casilla a mano.
 *
 * Full HTTP feature tests para estos 4 controladores requieren demasiado esquema (Client,
 * Property, Captacion+Operation, FormSubmission, auth) para este entorno de pruebas (mismo
 * criterio que otras partes del proyecto) — este test protege la regla en sí, por código fuente,
 * para que no se vuelva a quitar `after_or_equal:today` por accidente en ninguno de los 4 lugares.
 */
class VisitSchedulingPastDateGuardTest extends TestCase
{
    /** @return array<string, string> ruta => patrón del archivo */
    private function places(): array
    {
        return [
            'app/Http/Controllers/PropertyController.php' => "scheduleVisit",
            'app/Http/Controllers/Admin/CaptacionAdminController.php' => "scheduleVisit",
            'app/Http/Controllers/Admin/FormSubmissionController.php' => "scheduleVisit",
            'app/Http/Controllers/ClientController.php' => "storeInteraction",
        ];
    }

    public function test_every_visit_scheduling_endpoint_rejects_past_dates(): void
    {
        foreach ($this->places() as $relativePath => $methodName) {
            $source = file_get_contents(base_path($relativePath));
            $this->assertNotFalse($source, "No se pudo leer {$relativePath}");

            $this->assertStringContainsString(
                "scheduled_at_date'",
                $source,
                "{$relativePath} ya no valida scheduled_at_date — revisa si se movió a otro método."
            );
            $this->assertMatchesRegularExpression(
                "/'scheduled_at_date'\\s*=>\\s*'[^']*after_or_equal:today[^']*'/",
                $source,
                "{$relativePath}::{$methodName}() debe rechazar fechas pasadas con after_or_equal:today — sin esto se puede volver a mandar una confirmación de visita con fecha ya pasada (incidente real 2026-10-01)."
            );
        }
    }
}
