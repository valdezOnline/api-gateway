<?php

namespace App\Console\Commands;

use App\Jobs\MergeCardData;
use App\Jobs\MergeCardDataDeltas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MergeCardDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-card-data {--queue : Dispatch job to queue instead of running synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge UCR card data from staging table to actual table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('UCR Card Data Merge Process');
        $this->info('=============================');

        if ($this->option('queue')) {
            // Dispatch to queue
            $this->info('Dispatching merge job to queue...');
            MergeCardDataDeltas::dispatch();
            $this->info('✓ Job dispatched to queue successfully');
            Log::info('MergeCardData job dispatched to queue via artisan command');
        } else {
            // Run synchronously
            $this->info('Starting merge process (synchronous execution)...');

            try {
                $startTime = microtime(true);
                $job = new MergeCardDataDeltas();
                $job->handle();
                $endTime = microtime(true);

                $processingTime = round($endTime - $startTime, 2);
                $this->info("✓ Merge process completed successfully in {$processingTime} seconds");
                Log::info('MergeCardData job executed synchronously via artisan command', [
                    'processing_time' => $processingTime
                ]);

            } catch (\Exception $exception) {
                $this->error('✗ Merge process failed: ' . $exception->getMessage());
                Log::error('MergeCardData job failed via artisan command', [
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);
                return 1;
            }
        }

        $this->newLine();
        $this->info('For more information about the merge process, check the application logs.');

        return 0;
    }
}
