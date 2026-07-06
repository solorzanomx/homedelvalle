<?php

namespace App\Console\Commands;

use App\Models\Operation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackfillInquilinoOperations extends Command
{
    protected $signature = 'operations:backfill-inquilino
                            {--dry-run : Mostrar qué se haría sin escribir en la base de datos}';

    protected $description = 'Reclasifica Operations type=renta sin property_id (leads de arrendatario, nunca tuvieron una propiedad real asignada) a type=inquilino, su pipeline propio.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $operations = Operation::where('type', 'renta')
            ->whereNull('property_id')
            ->with('client')
            ->get();

        $this->info("Operations type=renta sin property_id: {$operations->count()}");

        if ($operations->isEmpty()) {
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($operations as $operation) {
            $newStage = in_array($operation->stage, Operation::INQUILINO_STAGES, true)
                ? $operation->stage
                : 'listo';

            $rows[] = [
                $operation->id,
                $operation->client->name ?? '(sin cliente)',
                $operation->stage,
                $newStage,
            ];

            if ($dryRun) {
                continue;
            }

            if ($newStage !== $operation->stage) {
                Log::info('BackfillInquilinoOperations: stage reasignado al reclasificar', [
                    'operation_id' => $operation->id,
                    'stage_anterior' => $operation->stage,
                    'stage_nuevo' => $newStage,
                ]);
            }

            $operation->update(['type' => 'inquilino', 'stage' => $newStage]);
        }

        $this->table(['Operation #', 'Cliente', 'Stage actual', 'Stage nuevo'], $rows);

        if ($dryRun) {
            $this->newLine();
            $this->line('<fg=yellow>Dry-run: no se escribió nada en la base de datos.</>');
        }

        return self::SUCCESS;
    }
}
