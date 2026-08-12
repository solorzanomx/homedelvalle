<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Services\ContractClauseSuggestionService;
use Illuminate\Console\Command;

class ProcessContractClauseSuggestions extends Command
{
    protected $signature = 'contracts:process-clause-suggestions';

    protected $description = 'Procesa las solicitudes pendientes de sugerencias de cláusulas por IA y las convierte en propuestas revisables por el broker.';

    public function handle(ContractClauseSuggestionService $service): int
    {
        $pending = Contract::where('ai_suggestion_status', 'pending')->get();

        foreach ($pending as $contract) {
            $service->generate($contract);
        }

        $this->info("Done. {$pending->count()} contrato(s) procesado(s).");

        return Command::SUCCESS;
    }
}
