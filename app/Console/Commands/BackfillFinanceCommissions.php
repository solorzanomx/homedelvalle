<?php

namespace App\Console\Commands;

use App\Models\Commission;
use App\Models\Operation;
use App\Models\Transaction;
use Illuminate\Console\Command;

class BackfillFinanceCommissions extends Command
{
    protected $signature = 'finance:backfill-commissions
                            {--dry-run : Mostrar qué se haría sin escribir en la base de datos}';

    protected $description = 'Genera Commission/Transaction para Operations ya completed con commission_amount capturado, que se cerraron antes de que OperationObserver empezara a registrarlas (auditoría 2026-07-06).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $operations = Operation::where('status', 'completed')
            ->whereNotNull('commission_amount')
            ->where('commission_amount', '>', 0)
            ->whereDoesntHave('commissions')
            ->with('client')
            ->get();

        $this->info("Operations completed con commission_amount sin Commission: {$operations->count()}");

        if ($operations->isEmpty()) {
            return self::SUCCESS;
        }

        $rows = [];

        foreach ($operations as $operation) {
            $rows[] = [
                $operation->id,
                $operation->client->name ?? '(sin cliente)',
                number_format((float) $operation->commission_amount, 0),
                $operation->completed_at?->format('d/m/Y') ?? '-',
            ];

            if ($dryRun) {
                continue;
            }

            $commission = Commission::create([
                'operation_id' => $operation->id,
                'broker_id'    => $operation->broker_id,
                'amount'       => $operation->commission_amount,
                'percentage'   => $operation->commission_percentage,
                'status'       => 'pending',
            ]);

            Transaction::create([
                'type'         => 'income',
                'category'     => 'commission',
                'description'  => "Comision — Operation #{$operation->id}",
                'amount'       => $operation->commission_amount,
                'date'         => ($operation->completed_at ?? now())->toDateString(),
                'operation_id' => $operation->id,
                'property_id'  => $operation->property_id,
                'broker_id'    => $operation->broker_id,
                'user_id'      => $operation->user_id,
            ]);
        }

        $this->table(['Operation #', 'Cliente', 'Comision', 'Cerrada el'], $rows);

        if ($dryRun) {
            $this->newLine();
            $this->line('<fg=yellow>Dry-run: no se escribió nada en la base de datos.</>');
        }

        return self::SUCCESS;
    }
}
