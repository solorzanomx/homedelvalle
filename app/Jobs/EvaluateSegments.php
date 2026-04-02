<?php

namespace App\Jobs;

use App\Services\SegmentService;
use App\Services\AutomationEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvaluateSegments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function handle(SegmentService $segmentService, AutomationEngine $engine): void
    {
        $stats = $segmentService->evaluateAll();

        Log::info('EvaluateSegments completed', $stats);

        // Trigger automations for newly entered segments
        if ($stats['entered'] > 0) {
            $recentEntries = \Illuminate\Support\Facades\DB::table('client_segment')
                ->where('entered_at', '>=', now()->subMinutes(10))
                ->whereNull('exited_at')
                ->get();

            foreach ($recentEntries as $entry) {
                $engine->processSegmentEnter($entry->segment_id, $entry->client_id);
            }
        }
    }
}
