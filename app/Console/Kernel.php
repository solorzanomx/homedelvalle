<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('social:publish-scheduled')->everyMinute()->withoutOverlapping();
        $schedule->command('blog:publish-scheduled')->everyMinute()->withoutOverlapping();
        $schedule->command('visits:send-reminders')->dailyAt('07:00');
        $schedule->command('leads:check-uncontacted')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('captaciones:check-exclusiva-pending')->dailyAt('09:00');
        $schedule->command('captaciones:check-valuacion-pendiente')->dailyAt('09:00');
        $schedule->job(new \App\Jobs\SendWeeklyPropertySummary)->weeklyOn(1, '08:00'); // Monday 8 AM
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
