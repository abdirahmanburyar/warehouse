<?php

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
        // Send low stock notification emails twice a day (9 AM and 3 PM)
        $schedule->command('inventory:notify-low-stock')->twiceDaily(9, 15);
        $schedule->command('inventory:check-low-stock')->everyFiveMinutes();
        
        // Schedule quarterly order generation on the last day of each quarter (Mar 31, Jun 30, Sep 30, Dec 31)
        $schedule->command('orders:generate-quarterly')
            ->quarterly()
            ->lastDayOfQuarter()
            ->at('23:00');
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
