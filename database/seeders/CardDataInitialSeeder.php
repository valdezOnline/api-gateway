<?php

namespace Database\Seeders;

use App\Models\UcrCardData;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
        UcrCardData::truncate();

        // Get the file information
        $nameOfFile = "UCR_CARD_DATA_INITIAL.csv";
        $pathOfFile = storage_path("app/public/uploads/$nameOfFile");
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
                    "load_status" => 'Initial'
                ];
                //UcrCardData::create($cardData);
                self::CreateRecord($cardData);
            }
            $withHeader = false;
        }
        fclose($inputFile);
    }

    public function CreateRecord($data)
    {
        try {
            // Create the data 
            UcrCardData::create($data);
        } catch (\Exception $ex) {
            $errMsg = $ex->getMessage();
            Log::error("CardDataInitialSeeder Error: $errMsg ", $ex->getTrace());
            return;
        }
    }
}
