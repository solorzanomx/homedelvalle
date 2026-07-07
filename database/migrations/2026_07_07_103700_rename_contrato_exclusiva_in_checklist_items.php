<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renombra "contrato de exclusiva"/"firmar la exclusiva" a "Acuerdo de
 * Representación" en los títulos/descripciones de checklist que lo
 * mencionan (etapas VISITA y EXCLUSIVA de captación). Busca por el
 * título viejo exacto — no toca operation_checklist_items ya marcados
 * (misma fila, solo cambian title/description).
 */
return new class extends Migration
{
    private const RENAMES = [
        [
            'stage' => 'visita',
            'old_title' => 'Ofrecer firmar la exclusiva en el momento si el propietario está listo',
            'new_title' => 'Ofrecer firmar el Acuerdo de Representación en el momento si el propietario está listo',
            'new_description' => 'Si el propietario está listo, no esperes — no hay mejor momento que el de máximo interés.',
        ],
        [
            'stage' => 'exclusiva',
            'old_title' => 'Generar el contrato de exclusiva en el sistema',
            'new_title' => 'Generar el Acuerdo de Representación en el sistema',
            'new_description' => 'Se genera directo desde el sistema con los datos ya capturados en las etapas anteriores.',
        ],
        [
            'stage' => 'exclusiva',
            'old_title' => 'Enviar el contrato al propietario',
            'new_title' => 'Enviar el Acuerdo de Representación al propietario',
            'new_description' => 'Envíalo y da seguimiento activo — no lo dejes "esperando a que firme solo".',
        ],
        [
            'stage' => 'exclusiva',
            'old_title' => 'Obtener la firma del contrato',
            'new_title' => 'Obtener la firma del Acuerdo de Representación',
            'new_description' => 'Este es el paso que convierte todo el trabajo anterior en un resultado real.',
        ],
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $r) {
            DB::table('stage_checklist_templates')
                ->where('operation_type', 'captacion')
                ->where('stage', $r['stage'])
                ->where('title', $r['old_title'])
                ->update(['title' => $r['new_title'], 'description' => $r['new_description']]);
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $r) {
            DB::table('stage_checklist_templates')
                ->where('operation_type', 'captacion')
                ->where('stage', $r['stage'])
                ->where('title', $r['new_title'])
                ->update(['title' => $r['old_title']]);
        }
    }
};
