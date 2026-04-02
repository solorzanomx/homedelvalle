<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\LeadScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateLeadScores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function handle(LeadScoringService $scoring): void
    {
        Client::select('id')->chunk(100, function ($clients) use ($scoring) {
            foreach ($clients as $client) {
                $scoring->recalculateProfileScore($client->id);
            }
        });
    }
}
