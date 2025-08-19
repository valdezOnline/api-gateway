<?php

namespace App\Console\Commands;

use App\Jobs\ProcessFileUpload;
use App\Models\FileLoad;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProcessFileUploadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-file-upload 
                            {file? : Specific file name to process}
                            {--queue : Dispatch job to queue instead of running synchronously}
                            {--all : Process all pending files}
                            {--status=pending : File status to filter by (pending, uploaded, processing)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process uploaded UCR card data files (IDMS Library or UCR Card Data files)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('UCR File Upload Processing');
        $this->info('==========================');

        // Get files to process
        $filesToProcess = $this->getFilesToProcess();

        if ($filesToProcess->isEmpty()) {
            $this->warn('No files found to process.');
            return 0;
        }

        $this->info("Found {$filesToProcess->count()} file(s) to process:");

        // Display files to be processed
        $this->table(
            ['ID', 'File Name', 'File Path', 'Status', 'Uploaded At'],
            $filesToProcess->map(function ($file) {
                return [
                    $file->id,
                    $file->fileName,
                    $file->filePath,
                    $file->status,
                    $file->created_at->format('Y-m-d H:i:s')
                ];
            })->toArray()
        );

        if (!$this->confirm('Do you want to proceed with processing these files?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Process each file
        $successCount = 0;
        $failureCount = 0;

        foreach ($filesToProcess as $fileLoad) {
            $this->newLine();
            $this->info("Processing file: {$fileLoad->fileName}");

            try {
                // Verify file exists
                $filePath = storage_path("app/public/uploads/{$fileLoad->fileName}");
                if (!File::exists($filePath)) {
                    $this->error("✗ File not found: {$filePath}");
                    $this->updateFileStatus($fileLoad, 'file_not_found');
                    $failureCount++;
                    continue;
                }

                // Update status to processing
                $this->updateFileStatus($fileLoad, 'processing');

                // Prepare file info for the job
                $fileInfo = [
                    'fileName' => $fileLoad->fileName,
                    'filePath' => $fileLoad->filePath,
                    'id' => $fileLoad->id
                ];

                if ($this->option('queue')) {
                    // Dispatch to queue
                    ProcessFileUpload::dispatch($fileInfo);
                    $this->info("✓ File dispatched to queue: {$fileLoad->fileName}");
                    Log::info('ProcessFileUpload job dispatched to queue', ['file' => $fileLoad->fileName]);
                } else {
                    // Run synchronously
                    $startTime = microtime(true);
                    $job = new ProcessFileUpload($fileInfo);
                    $job->handle();
                    $endTime = microtime(true);

                    $processingTime = round($endTime - $startTime, 2);
                    $this->info("✓ File processed successfully in {$processingTime} seconds: {$fileLoad->fileName}");
                    Log::info('ProcessFileUpload job executed synchronously', [
                        'file' => $fileLoad->fileName,
                        'processing_time' => $processingTime
                    ]);
                }

                $successCount++;

            } catch (\Exception $exception) {
                $this->error("✗ Failed to process file: {$fileLoad->fileName}");
                $this->error("Error: {$exception->getMessage()}");

                // Update file status to failed
                $this->updateFileStatus($fileLoad, 'failed', $exception->getMessage());

                Log::error('ProcessFileUpload job failed', [
                    'file' => $fileLoad->fileName,
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString()
                ]);

                $failureCount++;
            }
        }

        // Summary
        $this->newLine();
        $this->info('Processing Summary:');
        $this->info("✓ Successful: {$successCount}");
        if ($failureCount > 0) {
            $this->error("✗ Failed: {$failureCount}");
        }

        $this->newLine();
        $this->info('For detailed processing information, check the application logs.');

        return $failureCount > 0 ? 1 : 0;
    }

    /**
     * Get files to process based on command options
     */
    private function getFilesToProcess()
    {
        $fileName = $this->argument('file');
        $status = $this->option('status');
        $processAll = $this->option('all');

        $query = FileLoad::query();

        if ($fileName) {
            // Process specific file
            $query->where('fileName', $fileName);
        } elseif ($processAll) {
            // Process all files with specified status
            $query->where('status', $status);
        } else {
            // Process pending files by default
            $query->where('status', 'pending')
                ->orWhere('status', 'uploaded');
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    /**
     * Update file status in database
     */
    private function updateFileStatus(FileLoad $fileLoad, string $status, string $notes = null): void
    {
        try {
            $updateData = [
                'status' => $status,
                'updated_at' => now()
            ];

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            $fileLoad->update($updateData);
        } catch (\Exception $ex) {
            Log::error("Failed to update file status for {$fileLoad->fileName}: " . $ex->getMessage());
        }
    }
}
