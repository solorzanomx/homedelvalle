<?php

namespace App\Jobs;

use App\Services\SegmentService;
use App\Services\AutomationEngine;
use Illuminate\Support\Facades\Log;

class EvaluateSegments
{
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

        // Trigger automations for recently exited segments
        if (($stats['exited'] ?? 0) > 0) {
            $recentExits = \Illuminate\Support\Facades\DB::table('client_segment')
                ->where('exited_at', '>=', now()->subMinutes(10))
                ->get();

            foreach ($recentExits as $entry) {
                $engine->processSegmentExit($entry->segment_id, $entry->client_id);
            }
        }
    }
}
