<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:IpBlack-delete')->everyFiveMinutes()->withoutOverlapping()->when(function () {
            return env('IS_MASTER') == true;
        });
        $schedule->command('command:IpAllow-delete')->everyFiveMinutes()->withoutOverlapping()->when(function () {
            return env('IS_MASTER') == true;
        });
        $schedule->command('command:rule-black')->everyMinute()->withoutOverlapping()->when(function () {
            return env('IS_MASTER') == true;
        });
        // $schedule->command('command:web-active')->everyThirtyMinutes()->withoutOverlapping();
        // $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('command:iptables-allow-reload')->everyMinute()->withoutOverlapping();
        $schedule->command('command:iptables-black-reload')->everyMinute()->withoutOverlapping();
        $schedule->command('command:domain-parse')->everyThirtyMinutes()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
