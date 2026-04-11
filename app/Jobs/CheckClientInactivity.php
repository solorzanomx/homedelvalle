<?php

namespace App\Jobs;

use App\Models\Automation;
use App\Models\Client;
use App\Services\AutomationEngine;
use Illuminate\Support\Facades\Log;

class CheckClientInactivity
{
    public function handle(AutomationEngine $engine): void
    {
        $automations = Automation::active()
            ->where('trigger_type', 'inactivity')
            ->get();

        if ($automations->isEmpty()) return;

        $enrolled = 0;

        foreach ($automations as $automation) {
            $days = (int) ($automation->trigger_config['days'] ?? 7);

            $clients = Client::whereDoesntHave('leadEvents', function ($q) use ($days) {
                $q->where('occurred_at', '>=', now()->subDays($days));
            })->where('created_at', '<=', now()->subDays($days))->get();

            foreach ($clients as $client) {
                if ($engine->enroll($automation, $client)) {
                    $enrolled++;
                }
            }
        }

        Log::info("CheckClientInactivity: {$enrolled} clients enrolled");
    }
}
