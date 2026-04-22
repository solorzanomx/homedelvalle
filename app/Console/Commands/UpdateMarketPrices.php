<?php

namespace App\Console\Commands;

use App\Jobs\UpdateColoniaPricesJob;
use App\Models\MarketColonia;
use Illuminate\Console\Command;

class UpdateMarketPrices extends Command
{
    protected $signature = 'market:update-prices
                            {--colonia= : Actualizar solo una colonia por slug}
                            {--type=    : Tipo de inmueble: apartment|house (omitir = ambos)}
                            {--dry-run  : Mostrar qué se haría sin despachar jobs}';

    protected $description = 'Actualiza precios de mercado por colonia usando Perplexity';

    public function handle(): int
    {
        $coloniaSlug = $this->option('colonia');
        $typeOption  = $this->option('type');
        $dryRun      = $this->option('dry-run');

        $types = $typeOption
            ? [$typeOption]
            : ['apartment', 'house'];

        $query = MarketColonia::published()->with('zone');

        if ($coloniaSlug) {
            $query->where('slug', $coloniaSlug);
        }

        $colonias = $query->get();

        if ($colonias->isEmpty()) {
            $this->warn('No se encontraron colonias publicadas.');
            return self::FAILURE;
        }

        $this->info("Colonias a procesar: {$colonias->count()} · Tipos: " . implode(', ', $types));

        if ($dryRun) {
            $this->table(['Colonia', 'Zona', 'Tipos'], $colonias->map(fn($c) => [
                $c->name,
                $c->zone->name ?? '—',
                implode(', ', $types),
            ])->toArray());
            $this->line('<fg=yellow>Dry-run: no se despacharon jobs.</>');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($colonias->count());
        $bar->start();

        foreach ($colonias as $colonia) {
            UpdateColoniaPricesJob::dispatch($colonia, $types)
                ->onQueue('default')
                ->delay(now()->addSeconds(($colonias->search($colonia)) * 3)); // stagger 3s

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Despachados {$colonias->count()} jobs a la cola.");

        return self::SUCCESS;
    }
}
