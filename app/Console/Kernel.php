<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule($schedule)
    {
        // Keeps log_entries (admin dashboard live Logs panel) from growing
        // unbounded. Render's scheduler needs the "Run cron" add-on / a
        // scheduled job hitting `php artisan schedule:run` every minute for
        // this to actually fire — daily cron alone on Render won't call in.
        $schedule->command('logs:prune')->daily();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
