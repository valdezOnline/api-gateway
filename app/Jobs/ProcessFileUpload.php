<?php

namespace App\Jobs;

use App\Models\UcrCardData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use function fopen;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fileLoad;
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

        // Get the filename
        $nameOfFile = $this->fileLoad['fileName'];

        // Since this will be used for other uploaded files. We need to know which file to process

        // IDMS_Library (ucr_card_data)
        if (Str::contains($nameOfFile, 'IDMS_Library')) {
            // Process the UCR card data
            $withHeader = false;
            $pathOfFile = storage_path("app/public/uploads/$nameOfFile");

            $inputFile = fopen($pathOfFile, "r");

            // dd("basePath = $pathOfFile, inputFile = $inputFile");

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
                        "load_status" => $record['16']
                    ];
                    UcrCardData::create($cardData);
                }
                $withHeader = false;
            }
            fclose($inputFile);
        }

    }
}
