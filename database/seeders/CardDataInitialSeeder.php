<?php

namespace Database\Seeders;

use App\Models\UcrCardDataStaging;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CardDataInitialSeeder extends Seeder
{
    protected $startTime;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800; // 30 minutes

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info("Truncating data for a clean import.");
        // Truncate the current Table        
        UcrCardDataStaging::truncate();

        // Get the file information
        $nameOfFile = "UCR_CARD_DATA_FULL.csv";
        $pathOfFile = storage_path("app/public/uploads/$nameOfFile");

        // Check if the file exists
        if (!file_exists($pathOfFile)) {
            Log::error("CardDataInitialSeeder Error: File $pathOfFile does not exist.");
            return;
        }
        // Load the CSV file
        $this->LoadCsv($pathOfFile);
        // Import the CSV file
        // $this->ImportCsv($pathOfFile);
    }

    private function LoadCsv($pathOfFile)
    {
        try {

            // $withHeader = false;
            $inputFile = fopen($pathOfFile, "r");

            if (!$inputFile) {
                Log::error("Could not open file: $pathOfFile");
                return;
            }

            $bulkInsertData = [];
            // $bulkUpdateData = [];
            $batchSize = 250; // Reduced batch size for better memory management
            $recordCreated = 0;
            // $recordUpdated = 0;
            $totalProcessed = 0;
            $skipFirstRow = true; // Skip header if exists

            while (($record = fgetcsv($inputFile, 3000, ",")) !== false) {
                # code...
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
                // if (!$withHeader) {
                $cardData = [
                    "net_id" => $record[0],
                    "ssn" => $record[1],
                    "student_id" => $record[2],
                    "iso" => $record[3],
                    "lib_num" => $record[4],
                    "status1" => $record[5],
                    "class" => $record[6],
                    "yr_in_school" => $record[7],
                    "stud_fac" => $record[8],
                    "prox_int" => $record[9],
                    "prox_ext" => $record[10],
                    "prox_status" => $record[11],
                    "issued" => Str::length($record[12]) === 0 ? null : date_create_from_format('m/d/Y', $record['12']),
                    "edit_date" => Str::length($record[13]) === 0 ? null : date_create_from_format('m/d/Y', $record['13']),
                    "photo_date" => Str::length($record[14]) === 0 ? null : date_create_from_format('m/d/Y', $record['14']),
                    "imported" => Str::length($record[15]) === 0 ? null : date_create_from_format('m/d/Y', $record['15']),
                    "load_status" => 'Full'
                ];


                $recordCreated++;
                $totalProcessed++;
                $bulkInsertData[] = $cardData;

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

                // Check if we're approaching timeout (leave 5 minutes buffer)
                if ($this->timeout && (time() - $this->startTime) > ($this->timeout - 300)) {
                    Log::warning("Approaching timeout limit. Processed $totalProcessed records so far.");
                    break;
                }
                // }
                // $withHeader = false;
            }
            // Process remaining records
            if (!empty($bulkInsertData)) {
                $this->bulkInsertRecords($bulkInsertData);
            }

            // Close the file
            fclose($inputFile);
            Log::info("CARD Data Initial file processed: $recordCreated created, $totalProcessed total processed");

        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("CardDataInitialSeeder Error: $errMsg ", $ex->getTrace());
            return;
        }
    }

    public function bulkInsertRecords(array $data)
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
            // Create the data 
            // UcrCardDataStaging::create($data);
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("CardDataInitialSeeder Error: $errMsg ", $ex->getTrace());
            return;
        }
    }

    public function ImportCsv($pathOfFile)
    {
        try {
            $tableName = 'ucr_card_data_staging'; // Replace with your table name
            DB::statement("LOAD DATA INFILE '{$pathOfFile}'
                INTO TABLE {$tableName}
                FIELDS TERMINATED BY ',' 
                ENCLOSED BY '\"'
                LINES TERMINATED BY '\n'
                IGNORE 1 ROWS
                (net_id, ssn, student_id, iso, lib_num, status1, class, yr_in_school, stud_fac
                ,prox_int, prox_ext, prox_status, issued, edit_date, photo_date, imported)"); // Replace with your table columns  

        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("CardDataInitialSeeder Error: $errMsg ", $ex->getTrace());
            return;
        }


    }
}
