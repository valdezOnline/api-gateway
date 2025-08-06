<?php

namespace App\Jobs;

use App\Models\UcrCardData;
use App\Models\UcrCardDataStaging;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use function fopen;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileLoad;
    protected $startTime;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;
    /**
     * Create a new job instance.
     */
    public function __construct($fileInfo)
    {
        //
        $this->fileLoad = $fileInfo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->startTime = time(); // Track start time for timeout monitoring

        // Get the filename
        $nameOfFile = $this->fileLoad['fileName'];
        $pathOfFile = $this->fileLoad['filePath'];

        // Since this will be used for other uploaded files. We need to know which file to process

        // IDMS_Library_All_* (ucr_card_data)
        if (Str::contains($nameOfFile, 'IDMS_Library')) {
            $this->processIdmsLibraryFile($nameOfFile);
        }

        // UCR Card Data Initial or FULL file
        if (Str::contains($nameOfFile, 'UCR_CARD_DATA_INITIAL') || Str::contains($nameOfFile, 'UCR_CARD_DATA_FULL')) {
            $this->processUcrCardDataInitialFile($nameOfFile);
        }
    }

    /**
     * Process IDMS Library files (existing functionality)
     */
    private function processIdmsLibraryFile($nameOfFile): void
    {
        // Process the UCR card data
        $withHeader = false;
        $pathOfFile = storage_path("app/public/uploads/$nameOfFile");

        $inputFile = fopen($pathOfFile, "r");
        $recordUpdated = 0;
        $recordCreated = 0;
        $totalProcessed = 0;

        // Collect records for bulk operations
        $bulkInsertData = [];
        $bulkUpdateData = [];
        $batchSize = 250; // Reduced batch size for better memory management

        Log::info("Starting to process IDMS Library file: $nameOfFile");

        while (($record = fgetcsv($inputFile, 3000, ",")) !== false) {
            if (!$withHeader) {
                $cardData = [
                    "net_id" => $record['0'],
                    "ssn" => $record['1'],
                    "student_id" => $record['2'],
                    "iso" => $record['3'],
                    "lib_num" => $record['4'],
                    "status1" => $record['5'],
                    "class" => $record['6'],
                    "yr_in_school" => $record['7'],
                    "stud_fac" => $record['8'],
                    "prox_int" => $record['9'],
                    "prox_ext" => $record['10'],
                    "prox_status" => $record['11'],
                    "issued" => Str::length($record['12']) === 0 ? null : date_create_from_format('m/d/Y', $record['12']),
                    "edit_date" => Str::length($record['13']) === 0 ? null : date_create_from_format('m/d/Y', $record['13']),
                    "photo_date" => Str::length($record['14']) === 0 ? null : date_create_from_format('m/d/Y', $record['14']),
                    "imported" => Str::length($record['15']) === 0 ? null : date_create_from_format('m/d/Y', $record['15']),
                ];

                // Check if the record already exists
                $existingRecord = UcrCardDataStaging::where('net_id', $cardData['net_id'])
                    ->where('ssn', $cardData['ssn'])
                    ->where('student_id', $cardData['student_id'])
                    ->first();

                if ($existingRecord) {
                    $cardData['id'] = $existingRecord->id;
                    $cardData['updated_at'] = now();
                    $cardData['load_status'] = 'updated';
                    $bulkUpdateData[] = $cardData;
                    $recordUpdated++;
                } else {
                    $cardData['created_at'] = now();
                    $cardData['updated_at'] = now();
                    $cardData['load_status'] = 'created';
                    $bulkInsertData[] = $cardData;
                    $recordCreated++;
                }

                $totalProcessed++;

                // Process in batches
                if (count($bulkInsertData) >= $batchSize) {
                    $this->bulkInsertRecords($bulkInsertData);
                    $bulkInsertData = [];

                    // Log progress and free memory
                    if ($totalProcessed % 1000 === 0) {
                        Log::info("Processed $totalProcessed records from IDMS Library file");
                        gc_collect_cycles(); // Force garbage collection
                    }
                }

                if (count($bulkUpdateData) >= $batchSize) {
                    $this->bulkUpdateRecords($bulkUpdateData);
                    $bulkUpdateData = [];

                    // Log progress and free memory
                    if ($totalProcessed % 1000 === 0) {
                        Log::info("Processed $totalProcessed records from IDMS Library file");
                        gc_collect_cycles(); // Force garbage collection
                    }
                }

                // Check if we're approaching timeout (leave 5 minutes buffer)
                if ($this->timeout && (time() - $this->startTime) > ($this->timeout - 300)) {
                    Log::warning("Approaching timeout limit. Processed $totalProcessed records so far.");
                    break;
                }
            }
            $withHeader = false;
        }

        // Process remaining records
        if (!empty($bulkInsertData)) {
            $this->bulkInsertRecords($bulkInsertData);
        }

        if (!empty($bulkUpdateData)) {
            $this->bulkUpdateRecords($bulkUpdateData);
        }

        fclose($inputFile);
        Log::info("IDMS Library file processed: $recordCreated created, $recordUpdated updated, $totalProcessed total processed");
    }

    /**
     * Process UCR Card Data Initial files
     */
    private function processUcrCardDataInitialFile($nameOfFile): void
    {
        $pathOfFile = storage_path("app/public/uploads/$nameOfFile");

        if (!file_exists($pathOfFile)) {
            Log::error("File not found: $pathOfFile");
            return;
        }

        $inputFile = fopen($pathOfFile, "r");
        if (!$inputFile) {
            Log::error("Could not open file: $pathOfFile");
            return;
        }

        $bulkInsertData = [];
        $bulkUpdateData = [];
        $batchSize = 250; // Reduced batch size for better memory management
        $recordCreated = 0;
        $recordUpdated = 0;
        $totalProcessed = 0;
        $skipFirstRow = true; // Skip header if exists

        Log::info("Starting to process UCR Card Data Initial/Full file: $nameOfFile");
        Log::info("Truncating data for a clean import");

        // Truncate the current Table for a clean import
        UcrCardDataStaging::truncate();

        while (($record = fgetcsv($inputFile, 3000, ",")) !== false) {
            // Skip header row if it exists
            if ($skipFirstRow) {
                $skipFirstRow = false;
                // Check if first row contains headers
                if (strtolower($record[0]) === 'net_id' || strtolower($record[0]) === 'netid') {
                    continue;
                }
            }

            // Skip empty rows
            if (empty(array_filter($record))) {
                continue;
            }


            $cardData = [
                "net_id" => trim($record[0] ?? ''),
                "ssn" => trim($record[1] ?? ''),
                "student_id" => trim($record[2] ?? ''),
                "iso" => trim($record[3] ?? ''),
                "lib_num" => trim($record[4] ?? ''),
                "status1" => trim($record[5] ?? ''),
                "class" => trim($record[6] ?? ''),
                "yr_in_school" => trim($record[7] ?? ''),
                "stud_fac" => trim($record[8] ?? ''),
                "prox_int" => trim($record[9] ?? ''),
                "prox_ext" => trim($record[10] ?? ''),
                "prox_status" => trim($record[11] ?? ''),
                "issued" => $this->parseDate($record[12] ?? ''),
                "edit_date" => $this->parseDate($record[13] ?? ''),
                "photo_date" => $this->parseDate($record[14] ?? ''),
                "imported" => $this->parseDate($record[15] ?? ''),
            ];

            $recordCreated++;
            $bulkInsertData[] = $cardData;
            $totalProcessed++;

            // Process in smaller batches for better memory management
            if (count($bulkInsertData) >= $batchSize) {
                $this->bulkInsertRecords($bulkInsertData);
                $bulkInsertData = [];

                // Log progress and free memory
                if ($totalProcessed % 1000 === 0) {
                    Log::info("Processed $totalProcessed records from UCR Card Data Initial file");
                    gc_collect_cycles(); // Force garbage collection
                }
            }

            if (count($bulkUpdateData) >= $batchSize) {
                $this->bulkUpdateRecords($bulkUpdateData);
                $bulkUpdateData = [];

                // Log progress and free memory
                if ($totalProcessed % 1000 === 0) {
                    Log::info("Processed $totalProcessed records from UCR Card Data Initial file");
                    gc_collect_cycles(); // Force garbage collection
                }
            }

            // Check if we're approaching timeout (leave 5 minutes buffer)
            if ($this->timeout && (time() - $this->startTime) > ($this->timeout - 300)) {
                Log::warning("Approaching timeout limit. Processed $totalProcessed records so far.");
                break;
            }
        }

        // Process remaining records
        if (!empty($bulkInsertData)) {
            $this->bulkInsertRecords($bulkInsertData);
        }

        if (!empty($bulkUpdateData)) {
            $this->bulkUpdateRecords($bulkUpdateData);
        }

        fclose($inputFile);
        Log::info("UCR Card Data Initial file processed: $recordCreated created, $recordUpdated updated, $totalProcessed total processed");
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateString): ?string
    {
        $dateString = trim($dateString);

        if (empty($dateString)) {
            return null;
        }

        // Try different date formats
        $formats = ['m/d/Y', 'Y-m-d', 'd/m/Y', 'm-d-Y'];

        foreach ($formats as $format) {
            $date = date_create_from_format($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Bulk insert records
     */
    private function bulkInsertRecords(array $data): void
    {
        try {
            if (!empty($data)) {
                // Use chunks to avoid memory issues
                $chunks = array_chunk($data, 100);
                foreach ($chunks as $chunk) {
                    UcrCardDataStaging::insert($chunk);
                }
                Log::info("Bulk inserted " . count($data) . " records");
            }
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("ProcessFileUpload.bulkInsertRecords Error: $errMsg", $ex->getTrace());
        }
    }

    /**
     * Bulk update records
     */
    private function bulkUpdateRecords(array $data): void
    {
        try {
            if (!empty($data)) {
                DB::transaction(function () use ($data) {
                    $chunks = array_chunk($data, 50); // Smaller chunks for updates
                    foreach ($chunks as $chunk) {
                        foreach ($chunk as $record) {
                            $id = $record['id'];
                            unset($record['id']);
                            UcrCardDataStaging::where('id', $id)->update($record);
                        }
                    }
                });
                Log::info("Bulk updated " . count($data) . " records");
            }
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("ProcessFileUpload.bulkUpdateRecords Error: $errMsg", $ex->getTrace());
        }
    }

    private function CreateRecord($data)
    {
        try {
            // Create the data 
            UcrCardDataStaging::create($data);
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("ProcessFileUpload.CreateRecord Error: $errMsg ", $ex->getTrace());
            return;
        }
    }

    private function UpdateRecord($data)
    {
        try {
            // Update the data 
            UcrCardDataStaging::update($data);
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("ProcessFileUpload.UpdateRecord Error: $errMsg ", $ex->getTrace());
            return;
        }
    }
}
