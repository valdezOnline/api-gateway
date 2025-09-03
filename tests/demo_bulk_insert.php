<?php

/**
 * Demonstration script for ProcessFileUpload bulk insert functionality
 * This script shows how to use the newly enhanced ProcessFileUpload job
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';

use App\Jobs\ProcessFileUpload;
use App\Models\UcrCardDataStaging;

echo "=== ProcessFileUpload Bulk Insert Demonstration ===\n\n";

// File information for UCR_CARD_DATA_INITIAL.csv
$fileInfo = [
    'fileName' => 'UCR_CARD_DATA_INITIAL.csv',
    'filePath' => storage_path('app/public/uploads/UCR_CARD_DATA_INITIAL.csv'),
    'fileType' => 'csv',
    'fileSize' => filesize(storage_path('app/public/uploads/UCR_CARD_DATA_INITIAL.csv')),
    'createdBy' => 'demo_script'
];

echo "File: {$fileInfo['fileName']}\n";
echo "Path: {$fileInfo['filePath']}\n";
echo "Size: {$fileInfo['fileSize']} bytes\n";
echo "File exists: " . (file_exists($fileInfo['filePath']) ? 'Yes' : 'No') . "\n\n";

if (!file_exists($fileInfo['filePath'])) {
    echo "ERROR: File not found!\n";
    exit(1);
}

// Count lines in CSV
$lines = 0;
$handle = fopen($fileInfo['filePath'], 'r');
while (fgets($handle) !== false) {
    $lines++;
}
fclose($handle);
echo "Total lines in CSV: $lines\n\n";

// Count existing records before processing
$existingRecords = UcrCardDataStaging::count();
echo "Existing records in database: $existingRecords\n\n";

echo "Processing file...\n";
$startTime = microtime(true);

// Create and execute the job
$job = new ProcessFileUpload($fileInfo);
$job->handle();

$endTime = microtime(true);
$processingTime = round($endTime - $startTime, 2);

// Count records after processing
$totalRecords = UcrCardDataStaging::count();
$newRecords = $totalRecords - $existingRecords;

echo "\n=== Processing Results ===\n";
echo "Processing time: {$processingTime} seconds\n";
echo "Records before: $existingRecords\n";
echo "Records after: $totalRecords\n";
echo "New records added: $newRecords\n\n";

// Show some sample records
echo "=== Sample Records ===\n";
$sampleRecords = UcrCardDataStaging::take(5)->get(['net_id', 'ssn', 'student_id', 'status1', 'load_status']);
foreach ($sampleRecords as $record) {
    echo "Net ID: {$record->net_id}, SSN: {$record->ssn}, Status: {$record->status1}, Load Status: {$record->load_status}\n";
}

echo "\nDemo completed successfully!\n";
