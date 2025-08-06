<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Jobs\ProcessFileUpload;
use App\Models\UcrCardDataStaging;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the storage directory if it doesn't exist
        Storage::fake('public');
    }

    /** @test */
    public function it_can_process_ucr_card_data_initial_file()
    {
        // Create a sample CSV file content
        $csvContent = '"spaul049","862546832","862546832","6012730003270647","21210032706475","Graduate Student","G","D1","S","54105","","","09/20/2024","09/20/2024","09/20/2024","03/09/2024"' . "\n" .
            '"klin169","862547778","862547778","6012730003267254","21210032672545","Graduate Student","G","MR","S","53782","","","09/18/2024","09/30/2024","05/13/2025","03/09/2024"' . "\n" .
            '"mfei001","862478968","862478968","6012730003211328","21210032113284","Student","U","FR","S","48104","","","07/03/2024","07/03/2024","07/01/2024","03/09/2024"';

        // Create the file in storage
        $fileName = 'UCR_CARD_DATA_INITIAL_test.csv';
        $filePath = storage_path("app/public/uploads/$fileName");

        // Ensure the directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $csvContent);

        // Create file info array
        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'fileType' => 'csv',
            'fileSize' => filesize($filePath),
            'createdBy' => 'test_user'
        ];

        // Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert that records were created
        $this->assertDatabaseHas('ucr_card_data_staging', [
            'net_id' => 'spaul049',
            'ssn' => '862546832',
            'student_id' => '862546832'
        ]);

        $this->assertDatabaseHas('ucr_card_data_staging', [
            'net_id' => 'klin169',
            'ssn' => '862547778',
            'student_id' => '862547778'
        ]);

        $this->assertDatabaseHas('ucr_card_data_staging', [
            'net_id' => 'mfei001',
            'ssn' => '862478968',
            'student_id' => '862478968'
        ]);

        // Check that we have 3 records in total
        $this->assertEquals(3, UcrCardDataStaging::count());

        // Clean up
        unlink($filePath);
    }

    /** @test */
    public function it_handles_empty_csv_gracefully()
    {
        // Create an empty CSV file
        $fileName = 'UCR_CARD_DATA_INITIAL_empty.csv';
        $filePath = storage_path("app/public/uploads/$fileName");

        // Ensure the directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, '');

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'fileType' => 'csv',
            'fileSize' => 0,
            'createdBy' => 'test_user'
        ];

        // Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert no records were created
        $this->assertEquals(0, UcrCardDataStaging::count());

        // Clean up
        unlink($filePath);
    }

    /** @test */
    public function it_updates_existing_records()
    {
        // First create a record
        UcrCardDataStaging::create([
            'net_id' => 'spaul049',
            'ssn' => '862546832',
            'student_id' => '862546832',
            'iso' => 'old_iso',
            'load_status' => 'initial',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Create CSV content with updated data
        $csvContent = '"spaul049","862546832","862546832","6012730003270647","21210032706475","Graduate Student","G","D1","S","54105","","","09/20/2024","09/20/2024","09/20/2024","03/09/2024"';

        $fileName = 'UCR_CARD_DATA_INITIAL_update.csv';
        $filePath = storage_path("app/public/uploads/$fileName");

        // Ensure the directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => $filePath,
            'fileType' => 'csv',
            'fileSize' => filesize($filePath),
            'createdBy' => 'test_user'
        ];

        // Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert that the record was updated
        $this->assertDatabaseHas('ucr_card_data_staging', [
            'net_id' => 'spaul049',
            'ssn' => '862546832',
            'student_id' => '862546832',
            'iso' => '6012730003270647',
            'load_status' => 'updated'
        ]);

        // Ensure we still have only 1 record
        $this->assertEquals(1, UcrCardDataStaging::count());

        // Clean up
        unlink($filePath);
    }
}
