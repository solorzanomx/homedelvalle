<?php

namespace App\Console\Commands;

use App\Models\PropertyValuation;
use App\Services\Valuation\ValuationNarrativeService;
use Illuminate\Console\Command;

class GenerateValuationNarratives extends Command
{
    protected $signature = 'valuations:generate-narratives';

    protected $description = 'Genera (fuera del request HTTP) el análisis narrativo de IA de las valuaciones marcadas como pendientes, para evitar timeouts en hosting compartido.';

    public function handle(ValuationNarrativeService $service): int
    {
        $pending = PropertyValuation::where('narrative_status', 'pending')->get();

        foreach ($pending as $valuation) {
            $service->generate($valuation);
        }

        $this->info("Done. {$pending->count()} valuación(es) procesada(s).");

        return Command::SUCCESS;
    }
}
