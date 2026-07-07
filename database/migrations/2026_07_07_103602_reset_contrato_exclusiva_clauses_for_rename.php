<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renombrar "Contrato de Exclusiva" a "Acuerdo de Representación"
 * (ContratoExclusivaGeneratorService::DEFAULT_CLAUSES) no bastaba por sí
 * solo: 5 de las 6 cláusulas ya estaban guardadas en document_clauses
 * (editable desde /admin/documentos/contrato-exclusiva/clausulas) y
 * DocumentClause::text() prioriza ese valor guardado sobre el default del
 * código. config/document_registry.php ya documentaba que este contenido
 * estaba "pendiente de revisión por un abogado... no es definitivo" —
 * se borran para que vuelvan a tomar el nuevo default limpio.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('document_clauses')->where('document_key', 'contrato_exclusiva')->delete();
    }

    public function down(): void
    {
        // No se restaura el contenido viejo — el rename es intencional.
    }
};
