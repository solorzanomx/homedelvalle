<?php

namespace App\Console\Commands;

use App\Models\Captacion;
use App\Models\Operation;
use Illuminate\Console\Command;

class BackfillCaptacionOperations extends Command
{
    protected $signature = 'captaciones:backfill-operations
                            {--dry-run : Mostrar qué se haría sin escribir en la base de datos}';

    protected $description = 'Repara captaciones creadas antes del pipeline nuevo: crea la Operation vinculada si falta, y deriva target_type si falta (para que el auto-spawn funcione).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->backfillMissingOperations($dryRun);
        $this->newLine();
        $this->backfillMissingTargetType($dryRun);

        if ($dryRun) {
            $this->newLine();
            $this->line('<fg=yellow>Dry-run: no se escribió nada en la base de datos.</>');
        }

        return self::SUCCESS;
    }

    /**
     * Captaciones activas sin operation_id (ej. creadas desde el Portal del
     * Cliente antes de este fix) -> son invisibles en /admin/captaciones/pipeline.
     */
    private function backfillMissingOperations(bool $dryRun): void
    {
        $captaciones = Captacion::whereNull('operation_id')
            ->where('status', 'activo')
            ->with('client')
            ->get();

        $this->info("Captaciones activas sin Operation vinculada: {$captaciones->count()}");

        if ($captaciones->isEmpty()) {
            return;
        }

        $rows = [];
        foreach ($captaciones as $captacion) {
            $rows[] = [
                $captacion->id,
                $captacion->client->name ?? '(sin cliente)',
                $captacion->created_at->format('d/m/Y'),
            ];

            if ($dryRun) {
                continue;
            }

            $operation = Operation::create([
                'type'        => 'captacion',
                'stage'       => 'lead',
                'phase'       => 'captacion',
                'status'      => 'active',
                'property_id' => $captacion->property_id,
                'client_id'   => $captacion->client_id,
                'user_id'     => $captacion->client->assigned_user_id
                                 ?? \App\Models\User::where('role', '!=', 'client')->orderBy('id')->value('id'),
            ]);

            $captacion->update(['operation_id' => $operation->id]);
        }

        $this->table(['Captacion #', 'Cliente', 'Creada'], $rows);
    }

    /**
     * Operations de captacion activas sin target_type -> el auto-spawn de
     * venta/renta al llegar a carpeta_lista nunca se dispara para estas.
     */
    private function backfillMissingTargetType(bool $dryRun): void
    {
        $operations = Operation::where('type', 'captacion')
            ->where('status', 'active')
            ->whereNull('target_type')
            ->with('client')
            ->get();

        $captacionesByOperationId = Captacion::whereIn('operation_id', $operations->pluck('id'))
            ->get()
            ->keyBy('operation_id');

        $this->info("Operations de captación activas sin target_type: {$operations->count()}");

        if ($operations->isEmpty()) {
            return;
        }

        $rows = [];
        foreach ($operations as $operation) {
            $captacion  = $captacionesByOperationId->get($operation->id);
            $intent     = $captacion->intent ?? $operation->intent ?? 'general';
            $targetType = match ($intent) {
                'renta_residencial', 'renta_comercial' => 'renta',
                default => 'venta',
            };

            $rows[] = [
                $operation->id,
                $operation->client->name ?? '(sin cliente)',
                $intent,
                $targetType,
            ];

            if ($dryRun) {
                continue;
            }

            $operation->update(['target_type' => $targetType]);
        }

        $this->table(['Operation #', 'Cliente', 'Intent', 'target_type asignado'], $rows);
    }
}
