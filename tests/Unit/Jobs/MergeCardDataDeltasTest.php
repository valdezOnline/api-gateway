<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MergeCardDataDeltas;
use App\Models\UcrCardDataStaging;
use App\Models\UcrCardDataActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;
use PHPUnitrameworkattributestest;
use Carbon\Carbon;

class MergeCardDataDeltasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tables are clean before each test
        UcrCardDataStaging::truncate();
        UcrCardDataActual::truncate();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    #[Test]
    public function it_completes_successfully_when_no_records_need_processing()
    {
        // Arrange: Log expectations
        Log::shouldReceive('info')
            ->with('Starting UCR Card Data merge process')
            ->once();

        Log::shouldReceive('info')
            ->with('Analyze the deltas first.')
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Table delta analysis completed:/'))
            ->once();

        Log::shouldReceive('info')
            ->with('No records need processing. Tables are in sync.')
            ->once();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Tables should remain empty
        $this->assertEquals(0, UcrCardDataStaging::count());
        $this->assertEquals(0, UcrCardDataActual::count());
    }
    #[Test]
    public function it_inserts_new_records_from_staging_to_actual()
    {
        // Arrange: Create new records in staging
        $stagingRecord1 = UcrCardDataStaging::create([
            'net_id' => 'new001',
            'ssn' => '111111111',
            'student_id' => '111111111',
            'iso' => '1111111111111111',
            'lib_num' => '21111111111111111',
            'status1' => 'Active Student',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '11111',
            'prox_ext' => '11111',
            'prox_status' => 'A',
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $stagingRecord2 = UcrCardDataStaging::create([
            'net_id' => 'new002',
            'ssn' => '222222222',
            'student_id' => '222222222',
            'iso' => '2222222222222222',
            'lib_num' => '22222222222222222',
            'status1' => 'Active Student',
            'class' => 'G',
            'yr_in_school' => 'G1',
            'stud_fac' => 'S',
            'prox_int' => '22222',
            'prox_ext' => '22222',
            'prox_status' => 'A',
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Setup log expectations
        $this->setupDefaultLogExpectations();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Records should be inserted into actual table
        $this->assertEquals(2, UcrCardDataActual::count());

        $actualRecord1 = UcrCardDataActual::where('net_id', 'new001')->first();
        $this->assertNotNull($actualRecord1);
        $this->assertEquals('111111111', $actualRecord1->ssn);
        $this->assertEquals('Active Student', $actualRecord1->status1);

        $actualRecord2 = UcrCardDataActual::where('net_id', 'new002')->first();
        $this->assertNotNull($actualRecord2);
        $this->assertEquals('222222222', $actualRecord2->ssn);
        $this->assertEquals('G1', $actualRecord2->yr_in_school);
    }
    #[Test]
    public function it_updates_existing_records_in_actual_table()
    {
        // Arrange: Create an existing record in actual table
        $existingActual = UcrCardDataActual::create([
            'net_id' => 'update001',
            'ssn' => '333333333',
            'student_id' => '333333333',
            'iso' => '3333333333333333',
            'lib_num' => '23333333333333333',
            'status1' => 'Old Status',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '33333',
            'prox_ext' => '33333',
            'prox_status' => 'I',
            'load_status' => 'old',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay()
        ]);

        // Create updated record in staging with newer timestamp
        $stagingRecord = UcrCardDataStaging::create([
            'net_id' => 'update001',
            'ssn' => '333333333',
            'student_id' => '333333333',
            'iso' => '3333333333333333',
            'lib_num' => '23333333333333333',
            'status1' => 'Updated Status',
            'class' => 'G',
            'yr_in_school' => 'G2',
            'stud_fac' => 'S',
            'prox_int' => '44444',
            'prox_ext' => '44444',
            'prox_status' => 'A',
            'load_status' => 'updated',
            'created_at' => now()->subDay(),
            'updated_at' => now() // Newer timestamp
        ]);

        // Setup log expectations
        $this->setupDefaultLogExpectations();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Record should be updated in actual table
        $this->assertEquals(1, UcrCardDataActual::count());

        $updatedRecord = UcrCardDataActual::where('net_id', 'update001')->first();
        $this->assertNotNull($updatedRecord);
        $this->assertEquals('Updated Status', $updatedRecord->status1);
        $this->assertEquals('G', $updatedRecord->class);
        $this->assertEquals('G2', $updatedRecord->yr_in_school);
        $this->assertEquals('44444', $updatedRecord->prox_int);
        $this->assertEquals('A', $updatedRecord->prox_status);
    }
    #[Test]
    public function it_handles_mixed_insert_and_update_operations()
    {
        // Arrange: Create existing record in actual table
        UcrCardDataActual::create([
            'net_id' => 'existing001',
            'ssn' => '444444444',
            'student_id' => '444444444',
            'iso' => '4444444444444444',
            'lib_num' => '24444444444444444',
            'status1' => 'Existing Status',
            'class' => 'U',
            'yr_in_school' => 'U2',
            'stud_fac' => 'S',
            'prox_int' => '44444',
            'prox_ext' => '44444',
            'prox_status' => 'I',
            'load_status' => 'old',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay()
        ]);

        // Create staging records: one for update, one for insert
        UcrCardDataStaging::create([
            'net_id' => 'existing001',
            'ssn' => '444444444',
            'student_id' => '444444444',
            'iso' => '4444444444444444',
            'lib_num' => '24444444444444444',
            'status1' => 'Updated Status',
            'class' => 'G',
            'yr_in_school' => 'G1',
            'stud_fac' => 'S',
            'prox_int' => '55555',
            'prox_ext' => '55555',
            'prox_status' => 'A',
            'load_status' => 'updated',
            'created_at' => now()->subDay(),
            'updated_at' => now() // Newer timestamp
        ]);

        UcrCardDataStaging::create([
            'net_id' => 'new003',
            'ssn' => '555555555',
            'student_id' => '555555555',
            'iso' => '5555555555555555',
            'lib_num' => '25555555555555555',
            'status1' => 'New Student',
            'class' => 'U',
            'yr_in_school' => 'U3',
            'stud_fac' => 'S',
            'prox_int' => '66666',
            'prox_ext' => '66666',
            'prox_status' => 'A',
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Setup log expectations
        $this->setupDefaultLogExpectations();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Should have 2 records in actual table (1 updated, 1 inserted)
        $this->assertEquals(2, UcrCardDataActual::count());

        // Check updated record
        $updatedRecord = UcrCardDataActual::where('net_id', 'existing001')->first();
        $this->assertNotNull($updatedRecord);
        $this->assertEquals('Updated Status', $updatedRecord->status1);
        $this->assertEquals('G', $updatedRecord->class);

        // Check inserted record
        $insertedRecord = UcrCardDataActual::where('net_id', 'new003')->first();
        $this->assertNotNull($insertedRecord);
        $this->assertEquals('555555555', $insertedRecord->ssn);
        $this->assertEquals('New Student', $insertedRecord->status1);
    }
    #[Test]
    public function it_skips_records_with_older_timestamps()
    {
        // Arrange: Create newer record in actual table
        UcrCardDataActual::create([
            'net_id' => 'newer001',
            'ssn' => '666666666',
            'student_id' => '666666666',
            'iso' => '6666666666666666',
            'lib_num' => '26666666666666666',
            'status1' => 'Newer Status',
            'class' => 'G',
            'yr_in_school' => 'G3',
            'stud_fac' => 'S',
            'prox_int' => '66666',
            'prox_ext' => '66666',
            'prox_status' => 'A',
            'load_status' => 'current',
            'created_at' => now()->subDay(),
            'updated_at' => now() // Newer timestamp
        ]);

        // Create older record in staging
        UcrCardDataStaging::create([
            'net_id' => 'newer001',
            'ssn' => '666666666',
            'student_id' => '666666666',
            'iso' => '6666666666666666',
            'lib_num' => '26666666666666666',
            'status1' => 'Older Status',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '77777',
            'prox_ext' => '77777',
            'prox_status' => 'I',
            'load_status' => 'updated',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subHour() // Older timestamp
        ]);

        // Allow any log messages
        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Record should not be updated (staging is older)
        $this->assertEquals(1, UcrCardDataActual::count());

        $record = UcrCardDataActual::where('net_id', 'newer001')->first();
        $this->assertNotNull($record);
        $this->assertEquals('Newer Status', $record->status1); // Should remain unchanged
        $this->assertEquals('G', $record->class); // Should remain unchanged
    }
    #[Test]
    public function it_analyzes_table_deltas_correctly()
    {
        // Arrange: Create test data
        $oldTimestamp = now()->subDays(5);
        $newTimestamp = now();

        // Staging data
        UcrCardDataStaging::create([
            'net_id' => 'stage001',
            'ssn' => '111111111',
            'student_id' => '111111111',
            'iso' => '1111111111111111',
            'lib_num' => '21111111111111111',
            'status1' => 'Active',
            'load_status' => 'created',
            'created_at' => $newTimestamp,
            'updated_at' => $newTimestamp
        ]);

        // Actual data (older)
        UcrCardDataActual::create([
            'net_id' => 'actual001',
            'ssn' => '222222222',
            'student_id' => '222222222',
            'iso' => '2222222222222222',
            'lib_num' => '22222222222222222',
            'status1' => 'Active',
            'load_status' => 'old',
            'created_at' => $oldTimestamp,
            'updated_at' => $oldTimestamp
        ]);

        // Setup minimal log expectations
        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('analyzeTableDeltas');
        $method->setAccessible(true);
        $deltaAnalysis = $method->invoke($job);

        // Assert: Check delta analysis structure
        $this->assertIsArray($deltaAnalysis);
        $this->assertArrayHasKey('staging', $deltaAnalysis);
        $this->assertArrayHasKey('actual', $deltaAnalysis);
        $this->assertArrayHasKey('deltas', $deltaAnalysis);
        $this->assertArrayHasKey('merge_candidates', $deltaAnalysis);

        // Check counts
        $this->assertEquals(1, $deltaAnalysis['staging']['count']);
        $this->assertEquals(1, $deltaAnalysis['actual']['count']);

        // Check merge candidates
        $this->assertArrayHasKey('new_records', $deltaAnalysis['merge_candidates']);
        $this->assertArrayHasKey('updated_records', $deltaAnalysis['merge_candidates']);
        $this->assertArrayHasKey('total_to_process', $deltaAnalysis['merge_candidates']);
    }
    #[Test]
    public function it_prepares_record_data_correctly()
    {
        // Arrange: Create a staging record
        $stagingRecord = UcrCardDataStaging::create([
            'net_id' => 'test001',
            'ssn' => '123456789',
            'student_id' => '123456789',
            'iso' => '1234567890123456',
            'lib_num' => '21234567890123456',
            'status1' => 'Active Student',
            'class' => 'U',
            'yr_in_school' => 'U4',
            'stud_fac' => 'S',
            'prox_int' => '12345',
            'prox_ext' => '67890',
            'prox_status' => 'A',
            'issued' => now()->subDays(10),
            'edit_date' => now()->subDays(5),
            'photo_date' => now()->subDays(7),
            'imported' => now()->subDays(3),
            'load_status' => 'created'
        ]);

        // Act: Test the prepareRecordData method
        $job = new MergeCardDataDeltas();
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('prepareRecordData');
        $method->setAccessible(true);
        $preparedData = $method->invoke($job, $stagingRecord);

        // Assert: Check prepared data structure
        $expectedFields = [
            'net_id',
            'ssn',
            'student_id',
            'iso',
            'lib_num',
            'status1',
            'class',
            'yr_in_school',
            'stud_fac',
            'prox_int',
            'prox_ext',
            'prox_status',
            'issued',
            'edit_date',
            'photo_date',
            'imported',
            'load_status',
            'created_at',
            'updated_at'
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $preparedData);
        }

        // Check specific values
        $this->assertEquals('test001', $preparedData['net_id']);
        $this->assertEquals('123456789', $preparedData['ssn']);
        $this->assertEquals('Active Student', $preparedData['status1']);
        $this->assertEquals('created', $preparedData['load_status']);
    }
    #[Test]
    public function it_handles_database_transaction_rollback_on_error()
    {
        // This test verifies the job handles exceptions gracefully
        // In a real-world scenario, database errors would trigger rollback

        // Create staging data
        UcrCardDataStaging::create([
            'net_id' => 'error001',
            'ssn' => '999999999',
            'student_id' => '999999999',
            'iso' => '9999999999999999',
            'lib_num' => '29999999999999999',
            'status1' => 'Test Error',
            'load_status' => 'created'
        ]);

        // Allow any log messages
        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();
        Log::shouldReceive('error')->withAnyArgs()->zeroOrMoreTimes();

        // This test mainly verifies the structure is in place for error handling
        // Real database errors would be tested in integration tests
        $job = new MergeCardDataDeltas();
        $job->handle();

        // If we get here, the job completed without throwing an exception
        $this->assertEquals(1, UcrCardDataActual::count());
    }
    #[Test]
    public function it_processes_records_in_batches()
    {
        // Arrange: Create multiple staging records
        for ($i = 1; $i <= 5; $i++) {
            UcrCardDataStaging::create([
                'net_id' => "batch" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'ssn' => str_pad($i, 9, '0', STR_PAD_LEFT),
                'student_id' => str_pad($i, 9, '0', STR_PAD_LEFT),
                'iso' => str_repeat($i, 16),
                'lib_num' => '2' . str_repeat($i, 16),
                'status1' => 'Batch Test',
                'class' => 'U',
                'yr_in_school' => 'U1',
                'stud_fac' => 'S',
                'prox_int' => str_repeat($i, 5),
                'prox_ext' => str_repeat($i, 5),
                'prox_status' => 'A',
                'load_status' => 'created'
            ]);
        }

        // Setup log expectations
        $this->setupDefaultLogExpectations();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: All records should be processed
        $this->assertEquals(5, UcrCardDataActual::count());

        // Verify specific records
        for ($i = 1; $i <= 5; $i++) {
            $record = UcrCardDataActual::where('net_id', "batch" . str_pad($i, 3, '0', STR_PAD_LEFT))->first();
            $this->assertNotNull($record);
            $this->assertEquals(str_pad($i, 9, '0', STR_PAD_LEFT), $record->ssn);
        }
    }
    #[Test]
    public function it_handles_null_and_empty_timestamps_correctly()
    {
        // Arrange: Create records with null timestamps
        UcrCardDataStaging::create([
            'net_id' => 'null001',
            'ssn' => '777777777',
            'student_id' => '777777777',
            'iso' => '7777777777777777',
            'lib_num' => '27777777777777777',
            'status1' => 'Null Test',
            'load_status' => 'created',
            'created_at' => null,
            'updated_at' => null
        ]);

        // Setup log expectations
        $this->setupDefaultLogExpectations();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Record should be processed with current timestamps
        $this->assertEquals(1, UcrCardDataActual::count());

        $record = UcrCardDataActual::where('net_id', 'null001')->first();
        $this->assertNotNull($record);
        $this->assertNotNull($record->created_at);
        $this->assertNotNull($record->updated_at);
    }

    /**
     * Setup default log expectations for successful operations
     */
    private function setupDefaultLogExpectations(): void
    {
        Log::shouldReceive('info')
            ->with('Starting UCR Card Data merge process')
            ->once();

        Log::shouldReceive('info')
            ->with('Analyze the deltas first.')
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Table delta analysis completed:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/There are \d+ records to be inserted from staging/'))
            ->zeroOrMoreTimes();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/There are \d+ records to be updated in staging/'))
            ->zeroOrMoreTimes();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Gathering the records for insert to actual table/'))
            ->zeroOrMoreTimes();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Gathering records to update in actual table/'))
            ->zeroOrMoreTimes();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Processed \d+ records/'))
            ->zeroOrMoreTimes();

        Log::shouldReceive('info')
            ->with('UCR Card Data merge completed successfully')
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Processing time:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Total records processed:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Records inserted:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Records updated:/'))
            ->once();

        Log::shouldReceive('info')
            ->with(\Mockery::pattern('/Records skipped/'))
            ->once();
    }
}
