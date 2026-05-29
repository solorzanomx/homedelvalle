<?php

namespace App\Console\Commands;

use App\Jobs\UpdateZonePricesJob;
use App\Models\MarketZone;
use Illuminate\Console\Command;

class UpdateMarketPrices extends Command
{
    protected $signature = 'market:update-prices
                            {--zone=       : Actualizar solo una zona por slug}
                            {--operation=  : sale|rent|both (default: both)}
                            {--dry-run     : Mostrar qué se haría sin despachar jobs}';

    protected $description = 'Actualiza precios de mercado por zona usando Perplexity + Claude (corre el 1° de cada mes)';

    public function handle(): int
    {
        $zoneSlug  = $this->option('zone');
        $operation = $this->option('operation') ?? 'both';
        $dryRun    = $this->option('dry-run');

        $saleTypes = ['apartment', 'house'];
        $rentTypes = ['apartment', 'house', 'office'];

        $query = MarketZone::orderBy('sort_order');
        if ($zoneSlug) {
            $query->where('slug', $zoneSlug);
        }
        $zones = $query->get();

        if ($zones->isEmpty()) {
            $this->warn('No se encontraron zonas.');
            return self::FAILURE;
        }

        $ops = match($operation) {
            'sale' => ['sale'],
            'rent' => ['rent'],
            default => ['sale', 'rent'],
        };

        $total = $zones->count() * count($ops);
        $this->info("Zonas: {$zones->count()} · Operaciones: " . implode('+', $ops) . " · Jobs a despachar: {$total}");

        if ($dryRun) {
            $this->table(['Zona', 'Operaciones'], $zones->map(fn($z) => [
                $z->name,
                implode(', ', $ops),
            ])->toArray());
            $this->line('<fg=yellow>Dry-run: no se despacharon jobs.</>');
            return self::SUCCESS;
        }

        $bar   = $this->output->createProgressBar($total);
        $delay = 0;

        foreach ($zones as $zone) {
            if (in_array('sale', $ops)) {
                UpdateZonePricesJob::dispatch($zone, $saleTypes, 'sale')
                    ->onQueue('default')
                    ->delay(now()->addSeconds($delay));
                $delay += 15;
                $bar->advance();
            }
            if (in_array('rent', $ops)) {
                UpdateZonePricesJob::dispatch($zone, $rentTypes, 'rent')
                    ->onQueue('default')
                    ->delay(now()->addSeconds($delay));
                $delay += 15;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ {$total} jobs despachados. El proceso tardará ~" . round($delay / 60) . " minutos en completarse.");
        $this->line('  Monitorea en: /admin/market/prices');

        return self::SUCCESS;
    }
}
