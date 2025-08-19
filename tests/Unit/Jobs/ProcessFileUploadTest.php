<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessFileUpload;
use App\Models\FileLoad;
use App\Models\UcrCardDataStaging;
use App\Models\UcrCardDataActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;

class ProcessFileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tables are clean before each test
        UcrCardDataStaging::truncate();
        UcrCardDataActual::truncate();
        FileLoad::truncate();

        // Create actual uploads directory for testing since the job uses storage_path()
        $uploadsPath = storage_path('app/public/uploads');
        if (!file_exists($uploadsPath)) {
            mkdir($uploadsPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $uploadsPath = storage_path('app/public/uploads');
        if (file_exists($uploadsPath)) {
            $files = glob($uploadsPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_processes_idms_library_file_successfully()
    {
        // Arrange: Create a test IDMS Library CSV file
        $fileName = 'idms_library_test_file.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "jdoe001,123456789,123456789,1234567890123456,21234567890123456,Active Student,G,G1,S,12345,67890,A,01/15/2024,01/15/2024,01/15/2024,01/15/2024\n";
        $csvContent .= "jsmith002,987654321,987654321,9876543210987654,29876543210987654,Active Student,U,U4,S,54321,09876,A,02/15/2024,02/15/2024,02/15/2024,02/15/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify records were created in staging table
        $this->assertEquals(2, UcrCardDataStaging::count());

        $firstRecord = UcrCardDataStaging::where('net_id', 'jdoe001')->first();
        $this->assertNotNull($firstRecord);
        $this->assertEquals('123456789', $firstRecord->ssn);
        $this->assertEquals('Active Student', $firstRecord->status1);
        $this->assertEquals('created', $firstRecord->load_status);

        $secondRecord = UcrCardDataStaging::where('net_id', 'jsmith002')->first();
        $this->assertNotNull($secondRecord);
        $this->assertEquals('987654321', $secondRecord->ssn);
        $this->assertEquals('U4', $secondRecord->yr_in_school);
    }

    /** @test */
    public function it_processes_ucr_card_data_initial_file_successfully()
    {
        // Arrange: Create a test UCR Card Data Initial file
        $fileName = 'ucr_card_data_initial_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "initial001,111111111,111111111,1111111111111111,21111111111111111,New Student,U,U1,S,11111,11111,A,03/01/2024,03/01/2024,03/01/2024,03/01/2024\n";
        $csvContent .= "initial002,222222222,222222222,2222222222222222,22222222222222222,New Student,G,G2,S,22222,22222,A,03/02/2024,03/02/2024,03/02/2024,03/02/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify records were created in staging table
        $this->assertEquals(2, UcrCardDataStaging::count());

        $firstRecord = UcrCardDataStaging::where('net_id', 'initial001')->first();
        $this->assertNotNull($firstRecord);
        $this->assertEquals('111111111', $firstRecord->ssn);
        $this->assertEquals('New Student', $firstRecord->status1);
        $this->assertEquals('full-load', $firstRecord->load_status);

        // Verify it was merged to actual table since it's a full file
        $this->assertEquals(2, UcrCardDataActual::count());
        $actualRecord = UcrCardDataActual::where('net_id', 'initial001')->first();
        $this->assertNotNull($actualRecord);
        $this->assertEquals('111111111', $actualRecord->ssn);
    }

    /** @test */
    public function it_processes_ucr_card_data_full_file_successfully()
    {
        // Arrange: Create existing data in actual table
        UcrCardDataActual::create([
            'net_id' => 'existing001',
            'ssn' => '999999999',
            'student_id' => '999999999',
            'iso' => '9999999999999999',
            'lib_num' => '29999999999999999',
            'status1' => 'Old Student',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '99999',
            'prox_ext' => '99999',
            'prox_status' => 'I',
            'load_status' => 'old'
        ]);

        $fileName = 'ucr_card_data_full_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "full001,333333333,333333333,3333333333333333,23333333333333333,Full Load Student,G,G3,S,33333,33333,A,04/01/2024,04/01/2024,04/01/2024,04/01/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Verify initial state
        $this->assertEquals(1, UcrCardDataActual::count());

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify staging table was populated
        $this->assertEquals(1, UcrCardDataStaging::count());

        // Verify actual table was truncated and repopulated (old data gone, new data present)
        $this->assertEquals(1, UcrCardDataActual::count());
        $actualRecord = UcrCardDataActual::where('net_id', 'full001')->first();
        $this->assertNotNull($actualRecord);
        $this->assertEquals('333333333', $actualRecord->ssn);

        // Verify old record is gone
        $oldRecord = UcrCardDataActual::where('net_id', 'existing001')->first();
        $this->assertNull($oldRecord);
    }

    /** @test */
    public function it_handles_file_not_found_gracefully()
    {
        // Arrange: File info for non-existent file
        $fileInfo = [
            'fileName' => 'non_existent_file.csv',
            'filePath' => 'uploads/non_existent_file.csv'
        ];

        // Act: Process the non-existent file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: No records should be created
        $this->assertEquals(0, UcrCardDataStaging::count());
        $this->assertEquals(0, UcrCardDataActual::count());
    }

    /** @test */
    public function it_skips_header_rows_in_csv_files()
    {
        // Arrange: Create CSV with header row
        $fileName = 'ucr_card_data_initial_with_header.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "header001,444444444,444444444,4444444444444444,24444444444444444,Header Test,U,U2,S,44444,44444,A,05/01/2024,05/01/2024,05/01/2024,05/01/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Only data row should be processed (header skipped)
        $this->assertEquals(1, UcrCardDataStaging::count());
        $record = UcrCardDataStaging::first();
        $this->assertEquals('header001', $record->net_id);
        $this->assertNotEquals('net_id', $record->net_id); // Ensure header wasn't processed
    }

    /** @test */
    public function it_skips_empty_rows_in_csv_files()
    {
        // Arrange: Create CSV with empty rows
        $fileName = 'ucr_card_data_initial_with_empty_rows.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "empty001,555555555,555555555,5555555555555555,25555555555555555,Empty Test,U,U3,S,55555,55555,A,06/01/2024,06/01/2024,06/01/2024,06/01/2024\n";
        $csvContent .= ",,,,,,,,,,,,,,\n"; // Empty row
        $csvContent .= "empty002,666666666,666666666,6666666666666666,26666666666666666,Empty Test 2,G,G1,S,66666,66666,A,06/02/2024,06/02/2024,06/02/2024,06/02/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Only non-empty rows should be processed
        $this->assertEquals(2, UcrCardDataStaging::count());
        $this->assertDatabaseHas('ucr_card_data_staging', ['net_id' => 'empty001']);
        $this->assertDatabaseHas('ucr_card_data_staging', ['net_id' => 'empty002']);
    }

    /** @test */
    public function it_handles_date_parsing_correctly()
    {
        // Arrange: Create CSV with various date formats
        $fileName = 'ucr_card_data_initial_date_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "date001,777777777,777777777,7777777777777777,27777777777777777,Date Test,U,U4,S,77777,77777,A,07/15/2024,,07/10/2024,07/20/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify date handling
        $record = UcrCardDataStaging::where('net_id', 'date001')->first();
        $this->assertNotNull($record);
        $this->assertEquals('2024-07-15', $record->issued->format('Y-m-d'));
        $this->assertNull($record->edit_date); // Empty date should be null
        $this->assertEquals('2024-07-10', $record->photo_date->format('Y-m-d'));
        $this->assertEquals('2024-07-20', $record->imported->format('Y-m-d'));
    }

    /** @test */
    public function it_updates_file_load_record_after_processing()
    {
        // Arrange: Create initial FileLoad record
        $fileName = 'ucr_card_data_initial_file_load_test.csv';
        $filePath = 'uploads/ucr_card_data_initial_file_load_test.csv';

        FileLoad::create([
            'fileName' => $fileName,
            'filePath' => $filePath,
            'fileSize' => '1024',
            'fileType' => 'csv',
            'createdBy' => 'test_user',
            'status' => 'pending',
            'notes' => 'Initial upload'
        ]);

        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "fileload001,888888888,888888888,8888888888888888,28888888888888888,File Load Test,G,G4,S,88888,88888,A,08/01/2024,08/01/2024,08/01/2024,08/01/2024\n";

        $actualFilePath = storage_path("app/public/uploads/ucr_card_data_initial_file_load_test.csv");
        file_put_contents($actualFilePath, $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => $filePath
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify FileLoad record was updated
        $fileLoadRecord = FileLoad::where('fileName', $fileName)->first();
        $this->assertNotNull($fileLoadRecord);
        $this->assertEquals('processed', $fileLoadRecord->status);
        $this->assertStringContainsString('Processed 1 records', $fileLoadRecord->notes);
        $this->assertStringContainsString('Records created: 1', $fileLoadRecord->notes);
    }

    /** @test */
    public function it_handles_bulk_operations_efficiently()
    {
        // Arrange: Create a larger CSV file to test bulk operations
        $fileName = 'ucr_card_data_initial_bulk_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";

        // Generate 500 records to test bulk processing
        for ($i = 1; $i <= 500; $i++) {
            $paddedId = str_pad($i, 3, '0', STR_PAD_LEFT);
            $csvContent .= "bulk{$paddedId},{$paddedId}1111111,{$paddedId}1111111,{$paddedId}1111111111111,2{$paddedId}1111111111111,Bulk Test Student,U,U1,S,{$paddedId}11,{$paddedId}11,A,01/01/2024,01/01/2024,01/01/2024,01/01/2024\n";
        }

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify all records were processed
        $this->assertEquals(500, UcrCardDataStaging::count());

        // Check first and last records
        $firstRecord = UcrCardDataStaging::where('net_id', 'bulk001')->first();
        $this->assertNotNull($firstRecord);
        $this->assertEquals('0011111111', $firstRecord->ssn);

        $lastRecord = UcrCardDataStaging::where('net_id', 'bulk500')->first();
        $this->assertNotNull($lastRecord);
        $this->assertEquals('5001111111', $lastRecord->ssn);
    }

    /** @test */
    public function it_identifies_true_full_files_correctly()
    {
        // Test different file naming patterns to ensure proper identification
        $testCases = [
            'ucr_card_data_full_20240101.csv' => true,
            'ucr_card_data_initial_20240101.csv' => true,
            'ucr_card_data_initial_library_idms_20240101.csv' => false,
        ];

        foreach ($testCases as $fileName => $shouldMerge) {
            // Clean up between tests
            UcrCardDataStaging::truncate();
            UcrCardDataActual::truncate();

            $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
            $csvContent .= "test001,123456789,123456789,1234567890123456,21234567890123456,Test Student,U,U1,S,12345,67890,A,01/01/2024,01/01/2024,01/01/2024,01/01/2024\n";

            file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

            $fileInfo = [
                'fileName' => $fileName,
                'filePath' => 'uploads/' . $fileName
            ];

            // Act: Process the file
            $job = new ProcessFileUpload($fileInfo);
            $job->handle();

            // Assert: Check if data was merged to actual table based on expectation
            $this->assertEquals(1, UcrCardDataStaging::count());

            if ($shouldMerge) {
                $this->assertEquals(1, UcrCardDataActual::count(), "File $fileName should trigger merge to actual table");
            } else {
                $this->assertEquals(0, UcrCardDataActual::count(), "File $fileName should NOT trigger merge to actual table");
            }
        }
    }

    /** @test */
    public function it_handles_existing_records_in_idms_library_processing()
    {
        // Arrange: Create existing record in staging
        UcrCardDataStaging::create([
            'net_id' => 'existing_idms',
            'ssn' => '999999999',
            'student_id' => '999999999',
            'iso' => '9999999999999999',
            'lib_num' => '29999999999999999',
            'status1' => 'Old Status',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '99999',
            'prox_ext' => '99999',
            'prox_status' => 'I',
            'load_status' => 'old'
        ]);

        $fileName = 'idms_library_update_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "existing_idms,999999999,999999999,9999999999999999,29999999999999999,Updated Status,G,G1,S,11111,11111,A,01/01/2024,01/01/2024,01/01/2024,01/01/2024\n";
        $csvContent .= "new_idms,888888888,888888888,8888888888888888,28888888888888888,New Status,U,U2,S,22222,22222,A,01/02/2024,01/02/2024,01/02/2024,01/02/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Should have 2 records (1 updated, 1 new)
        $this->assertEquals(2, UcrCardDataStaging::count());

        // Check updated record
        $updatedRecord = UcrCardDataStaging::where('net_id', 'existing_idms')->first();
        $this->assertEquals('Updated Status', $updatedRecord->status1);
        $this->assertEquals('updated', $updatedRecord->load_status);

        // Check new record
        $newRecord = UcrCardDataStaging::where('net_id', 'new_idms')->first();
        $this->assertEquals('New Status', $newRecord->status1);
        $this->assertEquals('created', $newRecord->load_status);
    }

    /** @test */
    public function it_trims_whitespace_from_csv_fields()
    {
        // Arrange: Create CSV with whitespace in fields
        $fileName = 'ucr_card_data_initial_whitespace_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "  space001  ,  123456789  ,  123456789  ,  1234567890123456  ,  21234567890123456  ,  Test Student  ,  U  ,  U1  ,  S  ,  12345  ,  67890  ,  A  ,01/01/2024,01/01/2024,01/01/2024,01/01/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Verify whitespace was trimmed
        $record = UcrCardDataStaging::where('net_id', 'space001')->first();
        $this->assertNotNull($record);
        $this->assertEquals('space001', $record->net_id); // No leading/trailing spaces
        $this->assertEquals('123456789', $record->ssn);
        $this->assertEquals('Test Student', $record->status1);
        $this->assertEquals('U', $record->class);
    }

    /** @test */
    public function it_logs_processing_progress()
    {
        // This test verifies that appropriate log messages are generated
        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Starting to process file:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Detected UCR Card Data FULL\/Initial file:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Starting to process UCR Card Data Initial\/Full file:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/UCR Card Data file processed:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Updating FileLoad record with processed status/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Starting merge process from staging to actual table/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Found \d+ records in staging table/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Truncating ucr_card_data_actual table/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Successfully merged \d+ records/'))
            ->once();

        // Additional log calls that might occur
        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Arrange
        $fileName = 'ucr_card_data_full_log_test.csv';
        $csvContent = "net_id,ssn,student_id,iso,lib_num,status1,class,yr_in_school,stud_fac,prox_int,prox_ext,prox_status,issued,edit_date,photo_date,imported\n";
        $csvContent .= "log001,123456789,123456789,1234567890123456,21234567890123456,Log Test,U,U1,S,12345,67890,A,01/01/2024,01/01/2024,01/01/2024,01/01/2024\n";

        file_put_contents(storage_path("app/public/uploads/$fileName"), $csvContent);

        $fileInfo = [
            'fileName' => $fileName,
            'filePath' => 'uploads/' . $fileName
        ];

        // Act: Process the file
        $job = new ProcessFileUpload($fileInfo);
        $job->handle();

        // Assert: Mockery will verify the expected log calls were made
        $this->assertTrue(true); // If we get here, the log expectations were met
    }
}
