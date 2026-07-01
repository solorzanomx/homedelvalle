<?php

namespace App\Console\Commands;

use App\Models\Operation;
use App\Services\OperationChecklistService;
use Illuminate\Console\Command;

/**
 * Repara captaciones creadas antes del 30 de junio de 2026: su checklist
 * quedó sembrado con las plantillas genéricas de abril (operation_type=
 * 'both', ver 2026_04_02_000007_seed_default_checklist_templates.php) en
 * vez de las específicas de captación (operation_type='captacion', ver
 * 2026_06_30_910000_seed_captacion_checklist_templates.php). Los checklist
 * ya sembrados nunca se actualizan solos (initializeChecklistForStage()
 * no re-siembra si ya hay ítems), así que quedaron congelados con la
 * versión vieja. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
 */
class FixCaptacionChecklistGeneration extends Command
{
    protected $signature = 'captaciones:fix-checklist-generation
                            {--dry-run : Mostrar qué se haría sin escribir en la base de datos}';

    protected $description = 'Reemplaza el checklist de abril (genérico) por el de junio (específico de captación) en Operations de captación afectadas';

    public function handle(OperationChecklistService $checklistService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $affected = Operation::where('type', 'captacion')
            ->whereHas('checklistItems.template', fn ($q) => $q->where('operation_type', 'both'))
            ->with('client')
            ->get();

        $this->info("Operations de captación con checklist de abril: {$affected->count()}");

        if ($affected->isEmpty()) {
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($affected as $operation) {
            $wrongItems = $operation->checklistItems()
                ->whereHas('template', fn ($q) => $q->where('operation_type', 'both'))
                ->get();

            $affectedStages = $wrongItems->pluck('stage')->unique();

            $rows[] = [
                $operation->id,
                $operation->client->name ?? '(sin cliente)',
                $affectedStages->implode(', '),
                $wrongItems->count(),
            ];

            if ($dryRun) {
                continue;
            }

            $wrongItems->each->delete();

            foreach ($affectedStages as $stage) {
                $checklistService->initializeChecklistForStage($operation, $stage);
            }
        }

        $this->table(['Operation #', 'Cliente', 'Etapa(s) afectada(s)', 'Ítems de abril eliminados'], $rows);

        if ($dryRun) {
            $this->line('<fg=yellow>Dry-run: no se escribió nada en la base de datos.</>');
        }

        return self::SUCCESS;
    }
}
