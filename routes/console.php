<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Marketing Automation Scheduler ────────────────────
Schedule::job(new \App\Jobs\ProcessAutomationEnrollments)->everyMinute();
Schedule::job(new \App\Jobs\EvaluateSegments)->everyFiveMinutes();
Schedule::job(new \App\Jobs\RecalculateLeadScores)->daily();

// ── Blog Content Scheduler ───────────────────────────
Schedule::job(new \App\Jobs\PublishScheduledPosts)->everyMinute();
