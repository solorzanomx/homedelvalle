<?php

namespace App\Console\Commands;

use App\Models\Operation;
use App\Models\StageChecklistTemplate;
use App\Services\OperationChecklistService;
use Illuminate\Console\Command;

/**
 * Repara captaciones creadas antes del fix de 2026-07-01: la Operation
 * nacía directo en su etapa (normalmente 'lead') sin pasar nunca por
 * changeStage(), que es lo único que sembraba el checklist — dejando la
 * tarjeta "Checklist" vacía en la ficha. Ver docs/07-FLUJO-CAPTACION-Y-
 * MEJORAS.md y memoria de proyecto.
 */
class BackfillCaptacionChecklist extends Command
{
    protected $signature = 'captaciones:backfill-checklist
                            {--dry-run : Mostrar qué se haría sin escribir en la base de datos}';

    protected $description = 'Siembra el checklist faltante en la etapa actual de captaciones activas creadas antes del fix del 2026-07-01';

    public function handle(OperationChecklistService $checklistService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $operations = Operation::where('type', 'captacion')
            ->where('status', 'active')
            ->with('client')
            ->get();

        $rows = [];

        foreach ($operations as $operation) {
            $existing = $operation->checklistItems()->where('stage', $operation->stage)->count();
            if ($existing > 0) {
                continue;
            }

            $hasTemplates = StageChecklistTemplate::forStage($operation->stage, 'captacion')->exists();
            if (!$hasTemplates) {
                continue; // esta etapa genuinamente no tiene checklist configurado — no es el bug
            }

            $rows[] = [$operation->id, $operation->client->name ?? '(sin cliente)', $operation->stage];

            if ($dryRun) {
                continue;
            }

            $checklistService->initializeChecklistForStage($operation, $operation->stage);
        }

        $this->info("Operations de captación con checklist faltante: " . count($rows));

        if (!empty($rows)) {
            $this->table(['Operation #', 'Cliente', 'Etapa'], $rows);
        }

        if ($dryRun) {
            $this->line('<fg=yellow>Dry-run: no se escribió nada en la base de datos.</>');
        }

        return self::SUCCESS;
    }
}
