<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Marketing Automation Scheduler ────────────────────
Schedule::job(new \App\Jobs\ProcessAutomationEnrollments)->everyMinute()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('ProcessAutomationEnrollments scheduled run failed'));
Schedule::job(new \App\Jobs\EvaluateSegments)->everyFiveMinutes()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('EvaluateSegments scheduled run failed'));
Schedule::job(new \App\Jobs\RecalculateLeadScores)->daily()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('RecalculateLeadScores scheduled run failed'));
Schedule::job(new \App\Jobs\CheckClientInactivity)->dailyAt('06:00')->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('CheckClientInactivity scheduled run failed'));

// ── Blog Content Scheduler ───────────────────────────
// PublishScheduledPosts (job, bulk update sin aislamiento de error por post)
// se quito de aqui — duplicaba exactamente lo que hace blog:publish-scheduled
// (comando, try/catch por post, mejor aislamiento de fallos) — ambos corrian
// cada minuto (auditoria 2026-07-06).
Schedule::command('blog:publish-scheduled')->everyMinute()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('blog:publish-scheduled scheduled run failed'));

// ── EasyBroker: leads de portales → CRM ──────────────
Schedule::command('easybroker:sync-leads')->everyThirtyMinutes()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('easybroker:sync-leads scheduled run failed'));

// ── Google eSignature Scheduler ──────────────────────
Schedule::command('google:check-signatures')
    ->everyThirtyMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('google:check-signatures scheduled run failed'));

// ── Migrado desde app/Console/Kernel.php (2026-07-06) ────────────────────
// bootstrap/app.php registra `commands: routes/console.php` explicitamente
// (patron de Laravel 11+) y nunca enlaza App\Console\Kernel como el
// Contracts\Console\Kernel real — su metodo schedule() JAMAS se ejecutaba
// (confirmado con `php artisan schedule:list`, que solo listaba las tareas
// de este archivo). Los 6 comandos + 1 job de abajo llevaban quien sabe
// cuanto tiempo sin correr en produccion. Kernel.php se deja vacio con una
// nota — este archivo es ahora la unica fuente de verdad del schedule.
Schedule::command('social:publish-scheduled')->everyMinute()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('social:publish-scheduled scheduled run failed'));
Schedule::command('visits:send-reminders')->dailyAt('07:00')
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('visits:send-reminders scheduled run failed'));
Schedule::command('leads:check-uncontacted')->everyFifteenMinutes()->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('leads:check-uncontacted scheduled run failed'));
Schedule::command('captaciones:check-exclusiva-pending')->dailyAt('09:00')
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('captaciones:check-exclusiva-pending scheduled run failed'));
Schedule::command('captaciones:check-valuacion-pendiente')->dailyAt('09:00')
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('captaciones:check-valuacion-pendiente scheduled run failed'));
Schedule::job(new \App\Jobs\SendWeeklyPropertySummary)->weeklyOn(1, '08:00') // Monday 8 AM
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('SendWeeklyPropertySummary scheduled run failed'));

Schedule::command('market:update-prices')
    ->monthlyOn(1, '08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('market:update-prices scheduled run failed');
    });
