<?php

namespace App\Support;

/**
 * Campos que cuentan para el avance de cada sección del expediente (datos personales, identificación/domicilio,
 * ingresos). Una sola lista para el inquilino Y el obligado solidario: PortalExpedienteController::calcSections y
 * ObligadoSolidarioService::dataProgress leen de aquí, así no se desalinean.
 */
class ExpedienteFields
{
    // curp/rfc NO van en datos personales: se llenan en Identificación (hallazgo 2026-09-24).
    const PERSONAL = ['first_name', 'last_name_paterno', 'last_name_materno', 'birth_date', 'birth_state', 'gender', 'nationality', 'marital_status'];

    const IDENTIFICATION = ['id_type', 'curp', 'rfc', 'id_number', 'id_expiry_month', 'id_expiry_year', 'address_street', 'address_colony', 'address_municipality', 'address_state', 'address_zip'];

    /** Inquilino: datos laborales + cómo comprueba + arrendador anterior. */
    const INCOME_TENANT = ['income_type', 'income_amount', 'income_proof_type', 'employer_name', 'employer_phone', 'job_seniority', 'previous_landlord_name', 'previous_landlord_phone', 'previous_landlord_address'];

    /**
     * Obligado solidario: LOS MISMOS que el inquilino (nombre de su trabajo, teléfono, antigüedad, tipo e importe de
     * ingresos y ANTIGUO ARRENDADOR), salvo el "tipo de comprobante" (sus documentos van en Mis documentos).
     * Además de las 3 referencias personales, que cuentan aparte. Solo se deja fuera "información del hogar".
     */
    const INCOME_OBLIGADO = ['income_type', 'income_amount', 'employer_name', 'employer_phone', 'job_seniority', 'previous_landlord_name', 'previous_landlord_phone', 'previous_landlord_address'];

    /** Referencias personales que se piden (inquilino y obligado solidario). */
    const REFERENCES_REQUIRED = 3;
}
