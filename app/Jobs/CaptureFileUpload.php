<?php

namespace App\Jobs;

use App\Models\FileLoad;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaptureFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $uploadedFile;
    /**
     * Create a new job instance.
     */
    public function __construct($fileInfo)
    {
        //
        // dd($fileInfo);
        $this->uploadedFile = $fileInfo;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Record the uploaded file 
        FileLoad::Create([
            'filePath' => $this->uploadedFile['filePath'],
            'fileName' => $this->uploadedFile['fileName'],
            'fileType' => $this->uploadedFile['fileType'],
            'fileSize' => $this->uploadedFile['fileSize'],
            'createdBy' => $this->uploadedFile['createdBy'],
            'direction' => 'upload',
        ]);
    }
}
