<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * ESTA CLASE NO ESTA ENLAZADA — bootstrap/app.php usa el patron de Laravel
 * 11+ (`Application::configure(...)->withRouting(commands: routes/console.php)`)
 * y nunca liga App\Console\Kernel como el Contracts\Console\Kernel real, asi
 * que `schedule()` de aqui JAMAS se ejecuta. Confirmado con
 * `php artisan schedule:list`: solo listaba las tareas definidas en
 * routes/console.php. Hasta el 2026-07-06 esta clase tenia 7 tareas
 * programadas (blog:publish-scheduled, leads:check-uncontacted, las 2
 * alertas de captacion, etc) que llevaban quien sabe cuanto tiempo sin
 * correr en produccion — se migraron todas a routes/console.php, que es la
 * unica fuente de verdad real del schedule en esta app. No agregar nada
 * nuevo aqui; los comandos individuales (no el schedule) si se auto-
 * descubren solos desde app/Console/Commands sin necesitar esta clase.
 */
class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        //
    }
}
