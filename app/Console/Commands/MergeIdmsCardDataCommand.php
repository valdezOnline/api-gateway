<?php

namespace App\Console\Commands;

use App\Jobs\MergeIdmsCardDataFromStaging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MergeIdmsCardDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:merge-idms-card-data
                            {--queue : Dispatch to queue instead of running synchronously}
                            {--chunk-size= : DB chunk size (250-10000)}
                            {--full-sync : Truncate idms_card_data and mirror all rows from staging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge idms_card_data_staging into idms_card_data with selective updates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = $this->optionAsNullableInt('chunk-size');
        $fullSync = (bool) $this->option('full-sync');

        $this->info('IDMS merge process');
        $this->info('==================');

        if ($this->option('queue')) {
            $this->info('Dispatching merge job to queue...');

            $job = new MergeIdmsCardDataFromStaging($chunkSize, $fullSync);
            if (!empty(config('idms_sync.queue'))) {
                $job->onQueue(config('idms_sync.queue'));
            }

            dispatch($job);

            $this->info('Merge job dispatched successfully.');
            Log::info('MergeIdmsCardDataFromStaging job dispatched', [
                'chunk_size' => $chunkSize,
                'full_sync' => $fullSync,
            ]);

            return 0;
        }

        $this->info('Running merge synchronously...');

        try {
            $start = microtime(true);
            $job = new MergeIdmsCardDataFromStaging($chunkSize, $fullSync);
            app()->call([$job, 'handle']);
            $duration = round(microtime(true) - $start, 2);

            $this->info("Merge completed in {$duration} seconds.");

            Log::info('MergeIdmsCardDataFromStaging job executed synchronously', [
                'duration_seconds' => $duration,
                'chunk_size' => $chunkSize,
                'full_sync' => $fullSync,
            ]);

            return 0;
        } catch (\Throwable $throwable) {
            $this->error('Merge failed: ' . $throwable->getMessage());

            Log::error('MergeIdmsCardDataFromStaging job failed', [
                'error' => $throwable->getMessage(),
                'trace' => $throwable->getTraceAsString(),
            ]);

            return 1;
        }
    }

    private function optionAsNullableInt(string $optionName): ?int
    {
        $value = $this->option($optionName);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
