<?php

namespace App\Console\Commands;

use App\Models\Operation;
use App\Models\User;
use App\Services\OperationChecklistService;
use Illuminate\Console\Command;

class BackfillRentalProcesses extends Command
{
    protected $signature = 'rentals:backfill-processes
                            {--dry-run : Mostrar qué se haría sin escribir en la base de datos}';

    protected $description = 'Repara Operations de renta ya cerradas antes del puente Colocación->Post-Cierre: genera el RentalProcess faltante (ver OperationChecklistService::spawnRentalProcess).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $operations = Operation::where('type', 'renta')
            ->where('status', 'completed')
            ->whereDoesntHave('rentalProcess')
            ->with('client')
            ->get();

        $this->info("Operations de renta cerradas sin RentalProcess: {$operations->count()}");

        if ($operations->isEmpty()) {
            return self::SUCCESS;
        }

        $fallbackUser = User::where('role', '!=', 'client')->orderBy('id')->first();
        if (!$fallbackUser) {
            $this->error('No hay ningún usuario admin/broker en el sistema — no se puede continuar.');
            return self::FAILURE;
        }

        $rows = [];
        $service = app(OperationChecklistService::class);

        foreach ($operations as $operation) {
            $rows[] = [
                $operation->id,
                $operation->client->name ?? '(sin cliente)',
                $operation->completed_at?->format('d/m/Y') ?? '-',
            ];

            if ($dryRun) {
                continue;
            }

            $service->spawnRentalProcess($operation, $fallbackUser);
        }

        $this->table(['Operation #', 'Cliente', 'Cerrada el'], $rows);

        if ($dryRun) {
            $this->newLine();
            $this->line('<fg=yellow>Dry-run: no se escribió nada en la base de datos.</>');
        }

        return self::SUCCESS;
    }
}
