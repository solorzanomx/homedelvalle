<?php

namespace App\Jobs;

use App\Services\AutomationEngine;
use Illuminate\Support\Facades\Log;

class ProcessAutomationEnrollments
{
    public function handle(AutomationEngine $engine): void
    {
        $stats = $engine->processReadyEnrollments();

        Log::info('ProcessAutomationEnrollments completed', $stats);
    }
}
