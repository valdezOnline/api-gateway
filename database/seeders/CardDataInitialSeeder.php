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
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
        // $this->LoadCsv($pathOfFile);
        // Import the CSV file
        $this->ImportCsv($pathOfFile);
    }

    private function LoadCsv($pathOfFile)
    {
        try {
            $withHeader = false;
            $inputFile = fopen($pathOfFile, "r");
            while (($record = fgetcsv($inputFile, 3000, ",")) !== false) {
                # code...
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
                        "load_status" => 'Full'
                    ];
                    //UcrCardData::create($cardData);
                    self::CreateRecord($cardData);
                }
                $withHeader = false;
            }
            fclose($inputFile);

        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("CardDataInitialSeeder Error: $errMsg ", $ex->getTrace());
            return;
        }
    }

    public function CreateRecord($data)
    {
        try {
            // Create the data 
            UcrCardDataStaging::create($data);
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
