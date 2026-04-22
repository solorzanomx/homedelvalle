<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Marketing Automation Scheduler ────────────────────
Schedule::job(new \App\Jobs\ProcessAutomationEnrollments)->everyMinute()->withoutOverlapping();
Schedule::job(new \App\Jobs\EvaluateSegments)->everyFiveMinutes()->withoutOverlapping();
Schedule::job(new \App\Jobs\RecalculateLeadScores)->daily()->withoutOverlapping();
Schedule::job(new \App\Jobs\CheckClientInactivity)->dailyAt('06:00')->withoutOverlapping();

// ── Blog Content Scheduler ───────────────────────────
Schedule::job(new \App\Jobs\PublishScheduledPosts)->everyMinute()->withoutOverlapping();

// ── Market Observatory Scheduler ─────────────────────
Schedule::command('market:update-prices')
    ->monthlyOn(1, '08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('market:update-prices scheduled run failed');
    });
