<?php

namespace App\Jobs;

use App\Models\UcrCardDataStaging;
use App\Models\UcrCardDataActual;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MergeCardDataDeltas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job to merge data from ucr_card_data_staging to ucr_card_data_actual.
     * 
     * This job performs the following operations:
     * 1. Calculates a dynamic date range based on the timestamps from both staging and actual tables
     * 2. Identifies records in staging that have been created or updated within the calculated date range
     * 3. Compares staging records with actual table records based on created_at and updated_at fields
     * 4. Inserts new records from staging into the actual table
     * 5. Updates existing records in actual table with newer data from staging
     * 6. Logs the merge statistics
     */
    public function handle(): void
    {
        Log::info('Starting UCR Card Data merge process');

        $startTime = microtime(true);
        $recordsInserted = 0;
        $recordsUpdated = 0;
        $recordsProcessed = 0;
        $recordsSkipped = 0;
        $batchSize = 1000;

        // Analyze table deltas first
        Log::info('Analyze the deltas first.');
        $deltaAnalysis = $this->analyzeTableDeltas();
        Log::info('Table delta analysis completed: ' . json_encode($deltaAnalysis));

        // Determine processing strategy based on deltas
        if (
            isset($deltaAnalysis['merge_candidates']['total_to_process']) &&
            $deltaAnalysis['merge_candidates']['total_to_process'] === 0
        ) {
            Log::info('No records need processing. Tables are in sync.');
            return;
        }

        // Use delta information to optimize processing
        // $nDays = $this->calculateOptimalProcessingDays($deltaAnalysis);
        // $cutoffDate = now()->subDays($nDays);

        // Log::info("Processing records created or updated within the last {$nDays} days (since {$cutoffDate})");

        // Begin the transaction for merging data (updated records in staging)

        try {
            // Begin the transaction for merging data (updated records in staging)
            DB::beginTransaction();

            // Get the deltaAnalysis report for the new records
            $stagingNewRecordsCount = $deltaAnalysis['merge_candidates']['new_records'] ?? 0;
            Log::info("There are {$stagingNewRecordsCount} records to be inserted from staging.");
            if ($stagingNewRecordsCount !== 0) {
                Log::info('Gathering the records for insert to actual table.');
                UcrCardDataStaging::where('load_status', 'created')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('ucr_card_data_actual')
                            ->whereRaw('ucr_card_data_actual.load_status = ?', ['created'])
                            ->whereRaw('COALESCE(ucr_card_data_staging.updated_at, ucr_card_data_staging.created_at) > COALESCE(ucr_card_data_actual.updated_at, ucr_card_data_actual.created_at)');
                    })->chunk($batchSize, function ($stagingRecords) use (&$recordsInserted, &$recordsUpdated, &$recordsProcessed, &$recordsSkipped) {
                        foreach ($stagingRecords as $stagingRecord) {
                            // Prepare the record for insertion
                            $insertData = $this->prepareRecordData($stagingRecord);

                            // Insert the new record into the actual table
                            UcrCardDataActual::create($insertData);
                            $recordsInserted++;
                            // Record as processed
                            $recordsProcessed++;
                        }
                    });
            }

            // Get the deltaAnalysis report for the records to be updated
            $stagingUpdatedRecordsCount = $deltaAnalysis['merge_candidates']['updated_records'] ?? 0;
            Log::info("There are {$stagingUpdatedRecordsCount} records to be updated in staging.");
            // Check if there are records to update
            if ($stagingUpdatedRecordsCount !== 0) {
                Log::info('Gathering records to update in actual table.');
                // Get records that need to be updated
                UcrCardDataStaging::whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('ucr_card_data_actual')
                        ->whereRaw('ucr_card_data_actual.net_id = ucr_card_data_staging.net_id')
                        ->whereRaw('ucr_card_data_actual.ssn = ucr_card_data_staging.ssn')
                        ->whereRaw('ucr_card_data_actual.student_id = ucr_card_data_staging.student_id')
                        ->whereRaw('COALESCE(ucr_card_data_staging.updated_at, ucr_card_data_staging.created_at) > COALESCE(ucr_card_data_actual.updated_at, ucr_card_data_actual.created_at)');
                })->chunk($batchSize, function ($stagingRecords) use (&$recordsInserted, &$recordsUpdated, &$recordsProcessed, &$recordsSkipped) {
                    foreach ($stagingRecords as $stagingRecord) {

                        // Check if record exists in actual table
                        $existingRecord = UcrCardDataActual::where('net_id', $stagingRecord->net_id)
                            ->where('ssn', $stagingRecord->ssn)
                            ->where('student_id', $stagingRecord->student_id)
                            ->first();

                        if ($existingRecord) {
                            // Prepare data for update
                            $updateData = $this->prepareRecordData($stagingRecord);

                            // Update the existing record
                            $existingRecord->update($updateData);
                            // Record as processed
                            $recordsUpdated++;

                        } else {
                            // Record doesn't exist - prepare for insert
                            $insertData = $this->prepareRecordData($stagingRecord);
                            $insertData['load_status'] = 'created';

                            // Insert the new record into the actual table
                            UcrCardDataActual::create($insertData);
                            $recordsInserted++;
                            // Record as processed
                            $recordsProcessed++;
                        }
                    }
                });
            }



            // Log progress every 1000 records
            if ($recordsProcessed % 1000 === 0) {
                Log::info("Processed {$recordsProcessed} records - Inserted: {$recordsInserted}, Updated: {$recordsUpdated}, Skipped: {$recordsSkipped}");
                gc_collect_cycles(); // Force garbage collection for memory management
            }

            DB::commit();

            $endTime = microtime(true);
            $processingTime = round($endTime - $startTime, 2);

            Log::info("UCR Card Data merge completed successfully");
            Log::info("Processing time: {$processingTime} seconds");
            Log::info("Total records processed: {$recordsProcessed}");
            Log::info("Records inserted: {$recordsInserted}");
            Log::info("Records updated: {$recordsUpdated}");
            Log::info("Records skipped (no update needed): {$recordsSkipped}");

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("UCR Card Data merge failed: " . $exception->getMessage());
            Log::error("Stack trace: " . $exception->getTraceAsString());
            throw $exception;
        }
    }

    /**
     * Prepare record data for insertion or update.
     *
     * @param UcrCardDataStaging $record
     * @return array
     */
    private function prepareRecordData(UcrCardDataStaging $record): array
    {
        return [
            'net_id' => $record->net_id,
            'ssn' => $record->ssn,
            'student_id' => $record->student_id,
            'iso' => $record->iso,
            'lib_num' => $record->lib_num,
            'status1' => $record->status1,
            'class' => $record->class,
            'yr_in_school' => $record->yr_in_school,
            'stud_fac' => $record->stud_fac,
            'prox_int' => $record->prox_int,
            'prox_ext' => $record->prox_ext,
            'prox_status' => $record->prox_status,
            'issued' => $record->issued,
            'edit_date' => $record->edit_date,
            'photo_date' => $record->photo_date,
            'imported' => $record->imported,
            'load_status' => $record->load_status,
            'created_at' => $record->created_at ?: now(),
            'updated_at' => $record->updated_at ?: now(),
        ];
    }

    /**
     * Calculate the dynamic date range based on the most recent timestamps from both tables.
     * 
     * This method finds the most recent updated_at or created_at timestamp from both
     * staging and actual tables, then calculates the number of days between the oldest
     * and newest timestamps to determine the appropriate processing window.
     *
     * @return int Number of days for the processing window
     */
    // private function calculateDynamicDateRange(): int
    // {
    //     try {
    //         // Get the most recent timestamp from staging table
    //         $stagingLatest = UcrCardDataStaging::selectRaw('GREATEST(COALESCE(MAX(updated_at), "1970-01-01"), COALESCE(MAX(created_at), "1970-01-01")) as latest_timestamp')
    //             ->value('latest_timestamp');

    //         // Get the most recent timestamp from actual table
    //         $actualLatest = UcrCardDataActual::selectRaw('GREATEST(COALESCE(MAX(updated_at), "1970-01-01"), COALESCE(MAX(created_at), "1970-01-01")) as latest_timestamp')
    //             ->value('latest_timestamp');

    //         // Get the oldest timestamp from staging table
    //         $stagingOldest = UcrCardDataStaging::selectRaw('LEAST(COALESCE(MIN(updated_at), NOW()), COALESCE(MIN(created_at), NOW())) as oldest_timestamp')
    //             ->value('oldest_timestamp');

    //         // Get the oldest timestamp from actual table
    //         $actualOldest = UcrCardDataActual::selectRaw('LEAST(COALESCE(MIN(updated_at), NOW()), COALESCE(MIN(created_at), NOW())) as oldest_timestamp')
    //             ->value('oldest_timestamp');

    //         // Convert to Carbon instances for easier calculation
    //         $latestTimestamp = max($stagingLatest ?: now(), $actualLatest ?: now());
    //         $oldestTimestamp = min($stagingOldest ?: now(), $actualOldest ?: now());

    //         // Calculate the difference in days
    //         $diffInDays = now()->parse($latestTimestamp)->diffInDays(now()->parse($oldestTimestamp));

    //         // Ensure we have at least 1 day and maximum 365 days for safety
    //         $nDays = max(1, min($diffInDays, 365));

    //         Log::info("Dynamic date range calculated: {$nDays} days (from {$oldestTimestamp} to {$latestTimestamp})");

    //         return $nDays;

    //     } catch (\Exception $exception) {
    //         Log::warning("Failed to calculate dynamic date range, falling back to default: " . $exception->getMessage());
    //         // Fallback to a reasonable default if calculation fails
    //         return env('FALLBACK_MERGE_CARD_DATA_DAYS', 7); // Default to 7 days if not set
    //     }
    // }
    /**
     * Analyze the differences between staging and actual tables.
     *
     * @return array Detailed delta analysis
     */
    private function analyzeTableDeltas(): array
    {
        try {
            // Get record counts
            $stagingCount = UcrCardDataStaging::count();
            $actualCount = UcrCardDataActual::count();

            // Get timestamp analysis
            $stagingStats = UcrCardDataStaging::selectRaw('
            MAX(GREATEST(COALESCE(updated_at, created_at), COALESCE(created_at, updated_at))) as latest_timestamp,
            MIN(LEAST(COALESCE(updated_at, created_at), COALESCE(created_at, updated_at))) as oldest_timestamp,
            COUNT(*) as total_records
        ')->first();

            $actualStats = UcrCardDataActual::selectRaw('
            MAX(GREATEST(COALESCE(updated_at, created_at), COALESCE(created_at, updated_at))) as latest_timestamp,
            MIN(LEAST(COALESCE(updated_at, created_at), COALESCE(created_at, updated_at))) as oldest_timestamp,
            COUNT(*) as total_records
        ')->first();

            // Calculate time deltas
            $latestDelta = $stagingStats->latest_timestamp && $actualStats->latest_timestamp
                ? \Carbon\Carbon::parse($actualStats->latest_timestamp)->diffInSeconds(\Carbon\Carbon::parse($stagingStats->latest_timestamp), false)
                : 0;

            $oldestDelta = $stagingStats->oldest_timestamp && $actualStats->oldest_timestamp
                ? \Carbon\Carbon::parse($actualStats->oldest_timestamp)->diffInSeconds(\Carbon\Carbon::parse($stagingStats->oldest_timestamp), false)
                : 0;

            // Find records that exist in staging but not in actual. These are the new records with load_status of 'created'.
            $newRecordsCount = UcrCardDataStaging::where('load_status', 'created')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('ucr_card_data_actual')
                        ->whereRaw('ucr_card_data_actual.load_status = ?', ['created'])
                        ->whereRaw('COALESCE(ucr_card_data_staging.updated_at, ucr_card_data_staging.created_at) > COALESCE(ucr_card_data_actual.updated_at, ucr_card_data_actual.created_at)');
                })->count();

            // Find records that will need updates (staging updated_at > actual updated_at) and load_status of 'updated'
            $updatedRecordsCount = UcrCardDataStaging::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('ucr_card_data_actual')
                    ->whereRaw('ucr_card_data_actual.net_id = ucr_card_data_staging.net_id')
                    ->whereRaw('ucr_card_data_actual.ssn = ucr_card_data_staging.ssn')
                    ->whereRaw('ucr_card_data_actual.student_id = ucr_card_data_staging.student_id')
                    ->whereRaw('COALESCE(ucr_card_data_staging.updated_at, ucr_card_data_staging.created_at) > COALESCE(ucr_card_data_actual.updated_at, ucr_card_data_actual.created_at)');
            })->count();

            return [
                'staging' => [
                    'count' => $stagingCount,
                    'latest_timestamp' => $stagingStats->latest_timestamp,
                    'oldest_timestamp' => $stagingStats->oldest_timestamp
                ],
                'actual' => [
                    'count' => $actualCount,
                    'latest_timestamp' => $actualStats->latest_timestamp,
                    'oldest_timestamp' => $actualStats->oldest_timestamp
                ],
                'deltas' => [
                    'record_count_difference' => $stagingCount - $actualCount,
                    'latest_timestamp_delta_seconds' => $latestDelta,
                    'latest_timestamp_delta_hours' => round($latestDelta / 3600, 2),
                    'oldest_timestamp_delta_seconds' => $oldestDelta,
                    'staging_is_newer' => $latestDelta > 0,
                    'sync_status' => abs($latestDelta) <= 3600 ? 'in_sync' : 'out_of_sync'
                ],
                'merge_candidates' => [
                    'new_records' => $newRecordsCount,
                    'updated_records' => $updatedRecordsCount,
                    'total_to_process' => $newRecordsCount + $updatedRecordsCount
                ]
            ];

        } catch (\Exception $exception) {
            Log::error("Failed to analyze table deltas: " . $exception->getMessage());
            return ['error' => $exception->getMessage()];
        }
    }
    // private function calculateOptimalProcessingDays(array $deltaAnalysis): int
    // {
    //     if (isset($deltaAnalysis['error'])) {
    //         return env('FALLBACK_MERGE_CARD_DATA_DAYS', 7);
    //     }

    //     $latestDeltaHours = abs($deltaAnalysis['deltas']['latest_timestamp_delta_hours'] ?? 0);

    //     // If tables are very close in time, process less data
    //     if ($latestDeltaHours <= 24) {
    //         return 1;
    //     } elseif ($latestDeltaHours <= 168) { // 7 days
    //         return 7;
    //     } elseif ($latestDeltaHours <= 720) { // 30 days
    //         return 30;
    //     } else {
    //         return 90; // 3 months max
    //     }
    // }
}
