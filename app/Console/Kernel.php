<?php
declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('lead:create-proxy')->everyThirtyMinutes();
        $schedule->command('leads:send')->everyMinute();
        $schedule->command('generate:screen-shots')->everyTenMinutes();
        $schedule->command('telescope:prune --hours=48')->dailyAt('23:59');
        $schedule->command('lead:delete-proxy')->everyOddHour();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
