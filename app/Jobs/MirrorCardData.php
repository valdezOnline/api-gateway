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

class MirrorCardData implements ShouldQueue
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
     * Execute the job to mirror data from ucr_card_data_staging to ucr_card_data_actual.
     * 
     * This job performs a complete mirror operation:
     * 1. Truncates the actual table
     * 2. Copies all data from staging to actual table
     * 3. Logs the mirror statistics
     * 
     * This approach ensures the actual table is an exact copy of the staging table.
     */
    public function handle(): void
    {
        Log::info('Starting UCR Card Data mirror process');

        $startTime = microtime(true);
        $recordsMirrored = 0;
        $batchSize = 1000;

        try {

            // Get count of records in staging table
            $stagingCount = UcrCardDataStaging::count();
            Log::info("Records in staging table: {$stagingCount}");

            if ($stagingCount === 0) {
                Log::warning("No data in staging table. Truncating actual table and completing mirror process.");
                UcrCardDataActual::truncate();
                DB::commit();
                return;
            }

            // Get count of records in actual table before mirroring
            $actualCountBefore = UcrCardDataActual::count();
            Log::info("Records in actual table before mirror: {$actualCountBefore}");

            // Clear the actual table
            Log::info("Truncating actual table");
            UcrCardDataActual::truncate();

            // Copy all data from staging to actual in chunks for memory efficiency
            UcrCardDataStaging::chunk($batchSize, function ($stagingRecords) use (&$recordsMirrored) {
                DB::transaction(function () use ($stagingRecords, &$recordsMirrored) {
                    $insertData = [];

                    foreach ($stagingRecords as $record) {
                        $insertData[] = $this->prepareRecordData($record);
                    }

                    // Bulk insert the batch
                    UcrCardDataActual::insert($insertData);
                    $recordsMirrored += count($insertData);

                    // Log progress every batch
                    Log::info("Mirrored {$recordsMirrored} records so far");
                });

                // Force garbage collection for memory management
                gc_collect_cycles();
            });

            DB::commit();

            $endTime = microtime(true);
            $processingTime = round($endTime - $startTime, 2);

            // Verify the mirror was successful
            $actualCountAfter = UcrCardDataActual::count();

            Log::info("UCR Card Data mirror completed successfully");
            Log::info("Processing time: {$processingTime} seconds");
            Log::info("Records mirrored: {$recordsMirrored}");
            Log::info("Staging table count: {$stagingCount}");
            Log::info("Actual table count after mirror: {$actualCountAfter}");

            if ($stagingCount !== $actualCountAfter) {
                Log::warning("Record count mismatch detected! Staging: {$stagingCount}, Actual: {$actualCountAfter}");
            } else {
                Log::info("Mirror verification successful - record counts match");
            }

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("UCR Card Data mirror failed: " . $exception->getMessage());
            Log::error("Stack trace: " . $exception->getTraceAsString());
            throw $exception;
        }
    }

    /**
     * Prepare record data for insertion.
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
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];
    }
}
