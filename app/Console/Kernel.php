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
        $useQueue = (bool) config('idms_sync.use_queue', true);

        $stagingCommand = 'app:load-idms-card-data-staging' . ($useQueue ? ' --queue' : '');
        $mergeCommand = 'app:merge-idms-card-data' . ($useQueue ? ' --queue' : '');

        $schedule->command($stagingCommand)
            ->cron(config('idms_sync.staging.schedule_cron', '0 2 * * 1,3,5'))
            ->name('idms-card-data-staging-sync')
            ->withoutOverlapping();

        $schedule->command($mergeCommand)
            ->cron(config('idms_sync.merge.schedule_cron', '0 2 * * 2,4,6'))
            ->name('idms-card-data-merge-sync')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
