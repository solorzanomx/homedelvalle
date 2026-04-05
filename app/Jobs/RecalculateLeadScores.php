<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\LeadScoringService;

class RecalculateLeadScores
{
    public function handle(LeadScoringService $scoring): void
    {
        Client::select('id')->chunk(100, function ($clients) use ($scoring) {
            foreach ($clients as $client) {
                $scoring->recalculateProfileScore($client->id);
            }
        });
    }
}
