<?php

namespace App\Console\Commands;

use App\Jobs\LoadIdmsCardDataStaging;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LoadIdmsCardDataStagingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:load-idms-card-data-staging
                            {--queue : Dispatch to queue instead of running synchronously}
                            {--page-size= : API page size (1-5000)}
                            {--insert-chunk-size= : DB insert chunk size (100-5000)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh idms_card_data_staging with a full snapshot from IDMS API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pageSize = $this->optionAsNullableInt('page-size');
        $insertChunkSize = $this->optionAsNullableInt('insert-chunk-size');

        $this->info('IDMS staging refresh process');
        $this->info('============================');

        if ($this->option('queue')) {
            $this->info('Dispatching staging refresh job to queue...');

            $job = new LoadIdmsCardDataStaging($pageSize, $insertChunkSize);
            if (!empty(config('idms_sync.queue'))) {
                $job->onQueue(config('idms_sync.queue'));
            }

            dispatch($job);

            $this->info('Staging refresh job dispatched successfully.');
            Log::info('LoadIdmsCardDataStaging job dispatched', [
                'page_size' => $pageSize,
                'insert_chunk_size' => $insertChunkSize,
            ]);

            return 0;
        }

        $this->info('Running staging refresh synchronously...');

        try {
            $start = microtime(true);
            $job = new LoadIdmsCardDataStaging($pageSize, $insertChunkSize);
            $summary = app()->call([$job, 'handle']);
            $duration = round(microtime(true) - $start, 2);

            $this->info('✓ IDMS staging refresh completed successfully.');
            $this->info("Staging refresh completed in {$duration} seconds.");
            $this->info('Rows fetched: ' . ($summary['total_fetched'] ?? 0));
            $this->info('Rows inserted: ' . ($summary['total_inserted'] ?? 0));
            $this->info('Invalid rows skipped: ' . ($summary['invalid_rows'] ?? 0));

            Log::info('LoadIdmsCardDataStaging job executed synchronously', [
                'duration_seconds' => $duration,
                'page_size' => $pageSize,
                'insert_chunk_size' => $insertChunkSize,
                'summary' => $summary,
            ]);

            return 0;
        } catch (\Throwable $throwable) {
            $this->error('Staging refresh failed: ' . $throwable->getMessage());

            Log::error('LoadIdmsCardDataStaging job failed', [
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
