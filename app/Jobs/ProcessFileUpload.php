<?php

namespace App\Jobs;

use App\Models\FileLoad;
use App\Models\UcrCardData;
use App\Models\UcrCardDataStaging;
use App\Models\UcrCardDataActual;
use App\Jobs\MergeCardData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
        $executeDataMerge = false;

        Log::info("Starting to process file: $nameOfFile");

        // Since this will be used for other uploaded files. We need to know which file to process

        // IDMS_Library_* (this can either be the full file OR the deltas)
        if (Str::contains(Str::lower($nameOfFile), 'idms_library')) {
            Log::info("Detected IDMS Library file: $nameOfFile");
            $this->processIdmsLibraryFile(nameOfFile: $nameOfFile);
        }

        // UCR Card Data Initial/Full file
        if (Str::contains(Str::lower($nameOfFile), 'ucr_card_data_initial') || Str::contains(Str::lower($nameOfFile), 'ucr_card_data_full')) {
            Log::info("Detected UCR Card Data FULL/Initial file: $nameOfFile");

            // Check if this is truly a full file or an update file based on naming
            $isTrueFullFile = Str::contains(Str::lower($nameOfFile), 'ucr_card_data_full') ||
                (Str::contains(Str::lower($nameOfFile), 'ucr_card_data_initial') &&
                    !Str::contains(Str::lower($nameOfFile), 'library_idms'));

            $this->processUcrCardDataInitialFile($nameOfFile);
            $executeDataMerge = $isTrueFullFile; // Only merge if it's a true full file
        }

        // If this was a FULL/Initial file, merge data from staging to actual table
        if ($executeDataMerge) {
            $this->mergeDataToActualTable($nameOfFile);
        }

        // Move the successfully processed file to processed folder
        $this->moveFileToProcessedFolder($nameOfFile);

        Log::info("File processing completed successfully for: $nameOfFile");
    }

    /**
     * Process IDMS Library files 
     */
    private function processIdmsLibraryFile($nameOfFile): void
    {
        // Process the IDMS Library data
        $pathOfFile = storage_path("app/public/uploads/$nameOfFile");

        // Verify the file exists before processing
        Log::info("Verifying the file exists: $pathOfFile");
        if (!file_exists($pathOfFile)) {
            Log::error("File not found: $pathOfFile");
            return;
        }


        $inputFile = fopen($pathOfFile, "r");
        $recordUpdated = 0;
        $recordCreated = 0;
        $totalProcessed = 0;
        $skipFirstRow = true;

        // Collect records for bulk operations
        $bulkInsertData = [];
        $bulkUpdateData = [];
        $batchSize = 250; // Reduced batch size for better memory management

        Log::info("Starting to process IDMS Library file: $nameOfFile");

        while (($record = fgetcsv($inputFile, 3000, ",")) !== false) {

            // Check if first row contains headers            
            if ($skipFirstRow) {
                Log::info("Checking for header row in IDMS Library file");
                $skipFirstRow = false;
                Log::info("Assigned false to skipFirstRow to skip the record");
                // Check if first row contains headers
                Log::info("First record, first column value = $record[0]");
                if (strtolower($record[0]) === 'net_id') {
                    Log::info("Found header row in IDMS Library file");
                    continue;
                }
            }

            // Skip empty rows
            if (empty(array_filter($record))) {
                continue;
            }

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

        // Process remaining records
        if (!empty($bulkInsertData)) {
            $this->bulkInsertRecords($bulkInsertData);
        }

        if (!empty($bulkUpdateData)) {
            $this->bulkUpdateRecords($bulkUpdateData);
        }

        fclose($inputFile);
        Log::info("IDMS Library file processed: $recordCreated created, $recordUpdated updated, $totalProcessed total processed");

        // Update the FileLoad record
        Log::info("Updating FileLoad record with processed status");
        $this->updateFileLoadRecord($this->fileLoad['fileName'], $this->fileLoad['filePath'], $totalProcessed, $recordCreated, $recordUpdated);
    }

    /**
     * Process UCR Card Data Initial/Full files
     */
    private function processUcrCardDataInitialFile($nameOfFile): void
    {

        $pathOfFile = storage_path("app/public/uploads/$nameOfFile");

        // Verify the file exists before processing
        Log::info("Verifying the file exists: $pathOfFile");
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
        $batchSize = 200; // Reduced batch size for better memory management
        $recordCreated = 0;
        $recordUpdated = 0;
        $totalProcessed = 0;
        $skipFirstRow = true; // Skip header if exists

        // Start processing the file
        Log::info("Starting to process UCR Card Data Initial/Full file: $nameOfFile ");

        // Truncate the current Table for a clean initial / full import
        // Start processing the file
        Log::info("About to truncate UCR Card Data Staging table for clean import");
        UcrCardDataStaging::truncate();
        Log::info("Truncate completed. Starting to read records from the file.");

        while (($record = fgetcsv($inputFile, 3000, ",")) !== false) {

            // Skip empty rows first there were ocassions the upload process generated empty rows and caused issues.
            if (empty(array_filter($record))) {
                Log::debug("Skipping empty row");
                continue;
            }

            // Skip header row if it exists
            if ($skipFirstRow) {
                Log::info("Checking for header row in UCR Card Data file");
                $skipFirstRow = false;
                // Check if first row contains headers
                if (strtolower($record[0]) === 'net_id' || strtolower($record[1]) === 'ssn') {
                    Log::info("Found header row in UCR Card Data file, skipping it");
                    continue;
                }
                Log::debug("First record, first column value = $record[0]");
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
                "load_status" => 'full-load',
                "created_at" => now(),
                "updated_at" => now(),
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
                    Log::info("Processed $totalProcessed records from UCR Card Data file");
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

        fclose($inputFile);
        Log::info("UCR Card Data file processed: $recordCreated created, $recordUpdated updated, $totalProcessed total processed");
        // Update the FileLoad record
        Log::info("Updating FileLoad record with processed status");
        $this->updateFileLoadRecord($this->fileLoad['fileName'], $this->fileLoad['filePath'], $totalProcessed, $recordCreated, $recordUpdated);
    }

    /**
     * Update the fileLoad record in the database with the processed records information.
     */
    private function updateFileLoadRecord($fileName, $filePath, $totalProcessed, $recordCreated, $recordUpdated): void
    {
        try {
            FileLoad::where('fileName', $this->fileLoad['fileName'])->where('filePath', $this->fileLoad['filePath'])->update([
                'status' => 'processed',
                'notes' => "Processed $totalProcessed records in " . (time() - $this->startTime) . " seconds. Records created: $recordCreated, Records updated: $recordUpdated.",
                'updated_at' => now(),
            ]);
            Log::info("FileLoad record updated with status 'processed' for file: $fileName");
        } catch (\Exception $ex) {
            Log::error("Error updating FileLoad record for file($fileName): " . $ex->getMessage());
        }
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateString): ?string
    {
        $dateString = trim($dateString);

        if (empty($dateString) || $dateString === '0' || $dateString === '0000-00-00' || $dateString === '00/00/0000') {
            return null;
        }

        // Try different date formats
        $formats = ['m/d/Y', 'Y-m-d', 'd/m/Y', 'm-d-Y', 'n/j/Y', 'j/n/Y'];

        foreach ($formats as $format) {
            $date = date_create_from_format($format, $dateString);

            // Check if date parsing was successful and the date is valid
            if ($date !== false) {
                // Additional validation to ensure the date is reasonable
                $year = (int) $date->format('Y');
                $month = (int) $date->format('n');
                $day = (int) $date->format('j');

                // Validate date components
                if ($year >= 1900 && $year <= 2100 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31) {
                    // Verify the date actually exists (handles invalid dates like Feb 30)
                    if (checkdate($month, $day, $year)) {
                        // Return the date in Y-m-d format for database storage
                        return $date->format('Y-m-d');
                    }
                }
            }
        }

        // If no format worked, log the error and return null
        Log::warning("Could not parse date: '$dateString' with any of the supported formats");
        return null;
    }

    /**
     * Bulk insert records
     */
    private function bulkInsertRecords(array $data): void
    {
        try {
            if (!empty($data)) {
                // Clean and validate data before inserting
                $cleanData = $this->validateAndCleanData($data);

                if (empty($cleanData)) {
                    Log::warning("No valid records to insert after data validation");
                    return;
                }

                // Use chunks to avoid memory issues
                $chunks = array_chunk($cleanData, 100);
                $totalInserted = 0;

                foreach ($chunks as $chunkIndex => $chunk) {
                    try {
                        UcrCardDataStaging::insert($chunk);
                        $totalInserted += count($chunk);
                    } catch (\Exception $chunkEx) {
                        Log::error("Error inserting chunk $chunkIndex: " . $chunkEx->getMessage());

                        // Try inserting records one by one for this failed chunk
                        $this->insertRecordsIndividually($chunk, $chunkIndex);
                    }
                }

                Log::info("Bulk inserted $totalInserted out of " . count($data) . " records");
            }
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("ProcessFileUpload.bulkInsertRecords Error: $errMsg", $ex->getTrace());
        }
    }

    /**
     * Validate and clean data before insertion
     */
    private function validateAndCleanData(array $data): array
    {
        $cleanData = [];
        $invalidRecords = 0;

        foreach ($data as $record) {
            $cleanRecord = $record;
            $isValid = true;

            // Validate date fields and convert invalid dates to null
            $dateFields = ['issued', 'edit_date', 'photo_date', 'imported', 'created_at', 'updated_at'];

            foreach ($dateFields as $field) {
                if (isset($cleanRecord[$field])) {
                    $dateValue = $cleanRecord[$field];

                    // Handle various date formats and invalid dates
                    if (
                        $dateValue === '0000-00-00' || $dateValue === '0000-00-00 00:00:00' ||
                        $dateValue === '' || $dateValue === false
                    ) {
                        $cleanRecord[$field] = null;
                    } elseif (is_string($dateValue) && !empty($dateValue)) {
                        // Validate the date string
                        if (!$this->isValidDate($dateValue)) {
                            Log::warning("Invalid date '$dateValue' for field '$field', setting to null");
                            $cleanRecord[$field] = null;
                        }
                    }
                }
            }

            // Validate required fields
            if (empty($cleanRecord['net_id']) && empty($cleanRecord['ssn']) && empty($cleanRecord['student_id'])) {
                Log::warning("Skipping record with missing required identifiers");
                $invalidRecords++;
                $isValid = false;
            }

            if ($isValid) {
                $cleanData[] = $cleanRecord;
            }
        }

        if ($invalidRecords > 0) {
            Log::info("Filtered out $invalidRecords invalid records during data cleaning");
        }

        return $cleanData;
    }

    /**
     * Insert records individually when bulk insert fails
     */
    private function insertRecordsIndividually(array $chunk, int $chunkIndex): void
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($chunk as $recordIndex => $record) {
            try {
                UcrCardDataStaging::create($record);
                $successCount++;
            } catch (\Exception $recordEx) {
                $failCount++;
                Log::error("Failed to insert individual record in chunk $chunkIndex, record $recordIndex: " .
                    $recordEx->getMessage() . " - Record data: " . json_encode($record));
            }
        }

        Log::info("Individual insert for chunk $chunkIndex: $successCount successful, $failCount failed");
    }

    /**
     * Check if a date string is valid
     */
    private function isValidDate(string $dateString): bool
    {
        if (empty($dateString)) {
            return false;
        }

        // Try to parse with common formats
        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'm/d/Y', 'd/m/Y', 'm-d-Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date && $date->format($format) === $dateString) {
                // Check if it's not a zero date
                $year = (int) $date->format('Y');
                $month = (int) $date->format('m');
                $day = (int) $date->format('d');

                return $year > 0 && $month > 0 && $day > 0;
            }
        }

        return false;
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

    /**
     * Merge data from staging to actual table for FULL/Initial files
     */
    private function mergeDataToActualTable($nameOfFile): void
    {
        try {
            Log::info("Starting merge process from staging to actual table for file: $nameOfFile");

            // Check if staging table has data
            $stagingCount = UcrCardDataStaging::count();
            if ($stagingCount === 0) {
                Log::warning("No data found in staging table. Skipping merge process.");
                return;
            }

            Log::info("Found $stagingCount records in staging table. Beginning merge to actual table.");

            // Truncate the actual table for a clean import
            Log::info("Truncating ucr_card_data_actual table for clean import");
            UcrCardDataActual::truncate();

            // Use chunked processing to copy data from staging to actual
            $recordsProcessed = 0;
            $batchSize = 1000;

            UcrCardDataStaging::chunk($batchSize, function ($stagingRecords) use (&$recordsProcessed) {
                $insertData = [];

                foreach ($stagingRecords as $stagingRecord) {
                    $insertData[] = [
                        'net_id' => $stagingRecord->net_id,
                        'ssn' => $stagingRecord->ssn,
                        'student_id' => $stagingRecord->student_id,
                        'iso' => $stagingRecord->iso,
                        'lib_num' => $stagingRecord->lib_num,
                        'status1' => $stagingRecord->status1,
                        'class' => $stagingRecord->class,
                        'yr_in_school' => $stagingRecord->yr_in_school,
                        'stud_fac' => $stagingRecord->stud_fac,
                        'prox_int' => $stagingRecord->prox_int,
                        'prox_ext' => $stagingRecord->prox_ext,
                        'prox_status' => $stagingRecord->prox_status,
                        'issued' => $stagingRecord->issued,
                        'edit_date' => $stagingRecord->edit_date,
                        'photo_date' => $stagingRecord->photo_date,
                        'imported' => $stagingRecord->imported,
                        'load_status' => $stagingRecord->load_status,
                        'created_at' => $stagingRecord->created_at,
                        'updated_at' => $stagingRecord->updated_at,
                    ];
                }

                // Bulk insert into actual table
                if (!empty($insertData)) {
                    UcrCardDataActual::insert($insertData);
                    $recordsProcessed += count($insertData);
                    Log::info("Merged " . count($insertData) . " records to actual table. Total processed: $recordsProcessed");
                }

                // Memory management
                gc_collect_cycles();
            });

            Log::info("Successfully merged $recordsProcessed records from staging to actual table for file: $nameOfFile");

        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("Error merging data to actual table for file ($nameOfFile): $errMsg", $ex->getTrace());
            throw $ex; // Re-throw to ensure job failure is recorded
        }
    }

    /**
     * Move successfully processed file to processed folder
     */
    private function moveFileToProcessedFolder(string $fileName): void
    {
        try {
            $sourceFile = storage_path("app/public/uploads/$fileName");
            $processedDir = storage_path("app/public/uploads/processed");
            $destinationFile = "$processedDir/$fileName";

            // Create processed directory if it doesn't exist
            if (!File::exists($processedDir)) {
                File::makeDirectory($processedDir, 0755, true);
                Log::info("Created processed directory: $processedDir");
            }

            // Check if source file exists
            if (!File::exists($sourceFile)) {
                Log::warning("Source file not found for moving: $sourceFile");
                return;
            }

            // Add timestamp to filename to avoid conflicts
            $timestamp = now()->format('Y-m-d_H-i-s');
            $fileInfo = pathinfo($fileName);
            $newFileName = $fileInfo['filename'] . '_processed_' . $timestamp . '.' . $fileInfo['extension'];
            $destinationFile = "$processedDir/$newFileName";

            // Move the file
            if (File::move($sourceFile, $destinationFile)) {
                Log::info("Successfully moved file from $sourceFile to $destinationFile");

                // Update the FileLoad record with new location
                $this->updateFileLoadRecordLocation($fileName, "uploads/processed/$newFileName");
            } else {
                Log::error("Failed to move file from $sourceFile to $destinationFile");
            }

        } catch (\Exception $ex) {
            Log::error("Error moving file to processed folder ($fileName): " . $ex->getMessage(), $ex->getTrace());
            // Don't throw exception here as file processing was successful
            // Just log the error and continue
        }
    }

    /**
     * Update FileLoad record with new file location after moving
     */
    private function updateFileLoadRecordLocation(string $fileName, string $newFilePath): void
    {
        try {
            FileLoad::where('fileName', $fileName)->update([
                'filePath' => $newFilePath,
                'status' => 'processed_and_archived',
                'updated_at' => now(),
            ]);
            Log::info("Updated FileLoad record with new location: $newFilePath");
        } catch (\Exception $ex) {
            Log::error("Error updating FileLoad record location for file ($fileName): " . $ex->getMessage());
        }
    }
}
