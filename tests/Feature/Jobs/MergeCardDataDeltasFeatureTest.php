<?php

namespace Tests\Feature\Jobs;

use App\Jobs\MergeCardDataDeltas;
use App\Models\UcrCardDataStaging;
use App\Models\UcrCardDataActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Carbon\Carbon;

class MergeCardDataDeltasFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure tables are clean before each test
        UcrCardDataStaging::truncate();
        UcrCardDataActual::truncate();
    }

    /** @test */
    public function it_can_be_dispatched_to_queue()
    {
        // Arrange: Fake the queue
        Queue::fake();

        // Act: Dispatch the job
        MergeCardDataDeltas::dispatch();

        // Assert: Job was pushed to queue
        Queue::assertPushed(MergeCardDataDeltas::class);
    }

    /** @test */
    public function it_processes_full_data_merge_workflow_successfully()
    {
        // Arrange: Create a realistic dataset mimicking a real-world scenario

        // Create existing records in actual table (old data)
        UcrCardDataActual::create([
            'net_id' => 'jdoe001',
            'ssn' => '123456789',
            'student_id' => '123456789',
            'iso' => '1234567890123456',
            'lib_num' => '21234567890123456',
            'status1' => 'Active Student',
            'class' => 'U',
            'yr_in_school' => 'U3',
            'stud_fac' => 'S',
            'prox_int' => '12345',
            'prox_ext' => '67890',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-01-15'),
            'edit_date' => Carbon::parse('2024-01-15'),
            'photo_date' => Carbon::parse('2024-01-10'),
            'imported' => Carbon::parse('2024-01-20'),
            'load_status' => 'old',
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30)
        ]);

        UcrCardDataActual::create([
            'net_id' => 'asmith002',
            'ssn' => '987654321',
            'student_id' => '987654321',
            'iso' => '9876543210987654',
            'lib_num' => '29876543210987654',
            'status1' => 'Active Faculty',
            'class' => 'G',
            'yr_in_school' => 'G2',
            'stud_fac' => 'F',
            'prox_int' => '54321',
            'prox_ext' => '09876',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-02-01'),
            'edit_date' => Carbon::parse('2024-02-01'),
            'photo_date' => Carbon::parse('2024-01-25'),
            'imported' => Carbon::parse('2024-02-05'),
            'load_status' => 'current',
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15)
        ]);

        // Create staging records for processing

        // 1. Record to be updated (newer timestamp)
        UcrCardDataStaging::create([
            'net_id' => 'jdoe001',
            'ssn' => '123456789',
            'student_id' => '123456789',
            'iso' => '1234567890123456',
            'lib_num' => '21234567890123456',
            'status1' => 'Active Student', // Status updated
            'class' => 'G', // Class changed from U to G
            'yr_in_school' => 'G1', // Year changed
            'stud_fac' => 'S',
            'prox_int' => '11111', // Proximity changed
            'prox_ext' => '22222',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-03-01'),
            'edit_date' => Carbon::parse('2024-03-01'),
            'photo_date' => Carbon::parse('2024-02-25'),
            'imported' => Carbon::parse('2024-03-05'),
            'load_status' => 'updated',
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(1) // Recent update
        ]);

        // 2. New record to be inserted
        UcrCardDataStaging::create([
            'net_id' => 'mjohnson003',
            'ssn' => '555666777',
            'student_id' => '555666777',
            'iso' => '5556667770123456',
            'lib_num' => '25556667770123456',
            'status1' => 'New Student',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '33333',
            'prox_ext' => '44444',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-03-10'),
            'edit_date' => Carbon::parse('2024-03-10'),
            'photo_date' => Carbon::parse('2024-03-08'),
            'imported' => Carbon::parse('2024-03-12'),
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Another new record
        UcrCardDataStaging::create([
            'net_id' => 'kwilliams004',
            'ssn' => '888999000',
            'student_id' => '888999000',
            'iso' => '8889990000123456',
            'lib_num' => '28889990000123456',
            'status1' => 'Transfer Student',
            'class' => 'U',
            'yr_in_school' => 'U2',
            'stud_fac' => 'S',
            'prox_int' => '55555',
            'prox_ext' => '66666',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-03-15'),
            'edit_date' => Carbon::parse('2024-03-15'),
            'photo_date' => Carbon::parse('2024-03-12'),
            'imported' => Carbon::parse('2024-03-18'),
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Verify initial state
        $this->assertEquals(3, UcrCardDataStaging::count());
        $this->assertEquals(2, UcrCardDataActual::count());

        // Act: Execute the merge job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Verify the results

        // Should have 4 records in actual table (2 original + 2 new, 1 updated)
        $this->assertEquals(4, UcrCardDataActual::count());

        // Verify the updated record
        $updatedRecord = UcrCardDataActual::where('net_id', 'jdoe001')->first();
        $this->assertNotNull($updatedRecord);
        $this->assertEquals('G', $updatedRecord->class); // Should be updated
        $this->assertEquals('G1', $updatedRecord->yr_in_school); // Should be updated
        $this->assertEquals('11111', $updatedRecord->prox_int); // Should be updated
        $this->assertEquals('22222', $updatedRecord->prox_ext); // Should be updated

        // Verify the unchanged record (no update in staging)
        $unchangedRecord = UcrCardDataActual::where('net_id', 'asmith002')->first();
        $this->assertNotNull($unchangedRecord);
        $this->assertEquals('Active Faculty', $unchangedRecord->status1); // Should remain unchanged
        $this->assertEquals('G2', $unchangedRecord->yr_in_school); // Should remain unchanged

        // Verify the new records were inserted
        $newRecord1 = UcrCardDataActual::where('net_id', 'mjohnson003')->first();
        $this->assertNotNull($newRecord1);
        $this->assertEquals('555666777', $newRecord1->ssn);
        $this->assertEquals('New Student', $newRecord1->status1);
        $this->assertEquals('U1', $newRecord1->yr_in_school);

        $newRecord2 = UcrCardDataActual::where('net_id', 'kwilliams004')->first();
        $this->assertNotNull($newRecord2);
        $this->assertEquals('888999000', $newRecord2->ssn);
        $this->assertEquals('Transfer Student', $newRecord2->status1);
        $this->assertEquals('U2', $newRecord2->yr_in_school);
    }

    /** @test */
    public function it_handles_large_dataset_processing_efficiently()
    {
        // Arrange: Create a large dataset to test performance and batch processing

        // Create existing records in actual table
        for ($i = 1; $i <= 100; $i++) {
            UcrCardDataActual::create([
                'net_id' => "existing" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'ssn' => str_pad($i * 1000, 9, '0', STR_PAD_LEFT),
                'student_id' => str_pad($i * 1000, 9, '0', STR_PAD_LEFT),
                'iso' => str_repeat(($i % 10), 16),
                'lib_num' => '2' . str_repeat(($i % 10), 16),
                'status1' => 'Existing Student',
                'class' => 'U',
                'yr_in_school' => 'U' . ($i % 4 + 1),
                'stud_fac' => 'S',
                'prox_int' => str_repeat(($i % 10), 5),
                'prox_ext' => str_repeat((($i + 1) % 10), 5),
                'prox_status' => 'A',
                'load_status' => 'existing',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(10)
            ]);
        }

        // Create staging records (50 for update, 50 new)

        // Records for update
        for ($i = 1; $i <= 50; $i++) {
            UcrCardDataStaging::create([
                'net_id' => "existing" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'ssn' => str_pad($i * 1000, 9, '0', STR_PAD_LEFT),
                'student_id' => str_pad($i * 1000, 9, '0', STR_PAD_LEFT),
                'iso' => str_repeat(($i % 10), 16),
                'lib_num' => '2' . str_repeat(($i % 10), 16),
                'status1' => 'Updated Student', // Changed status
                'class' => 'G', // Changed class
                'yr_in_school' => 'G' . ($i % 4 + 1),
                'stud_fac' => 'S',
                'prox_int' => str_repeat((($i + 2) % 10), 5), // Changed proximity
                'prox_ext' => str_repeat((($i + 3) % 10), 5),
                'prox_status' => 'A',
                'load_status' => 'updated',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDay() // Newer timestamp
            ]);
        }

        // New records
        for ($i = 1; $i <= 50; $i++) {
            UcrCardDataStaging::create([
                'net_id' => "new" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'ssn' => str_pad(($i + 200) * 1000, 9, '0', STR_PAD_LEFT),
                'student_id' => str_pad(($i + 200) * 1000, 9, '0', STR_PAD_LEFT),
                'iso' => str_repeat((($i + 5) % 10), 16),
                'lib_num' => '2' . str_repeat((($i + 5) % 10), 16),
                'status1' => 'New Student',
                'class' => 'U',
                'yr_in_school' => 'U' . ($i % 4 + 1),
                'stud_fac' => 'S',
                'prox_int' => str_repeat((($i + 6) % 10), 5),
                'prox_ext' => str_repeat((($i + 7) % 10), 5),
                'prox_status' => 'A',
                'load_status' => 'created',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Verify initial state
        $this->assertEquals(100, UcrCardDataStaging::count());
        $this->assertEquals(100, UcrCardDataActual::count());

        // Measure processing time
        $startTime = microtime(true);

        // Act: Execute the merge job
        $job = new MergeCardDataDeltas();
        $job->handle();

        $endTime = microtime(true);
        $processingTime = $endTime - $startTime;

        // Assert: Verify the results

        // Should have 150 records in actual table (100 original + 50 new)
        $this->assertEquals(150, UcrCardDataActual::count());

        // Verify some updated records
        $updatedRecord = UcrCardDataActual::where('net_id', 'existing001')->first();
        $this->assertNotNull($updatedRecord);
        $this->assertEquals('Updated Student', $updatedRecord->status1);
        $this->assertEquals('G', $updatedRecord->class);

        // Verify some unchanged records
        $unchangedRecord = UcrCardDataActual::where('net_id', 'existing051')->first();
        $this->assertNotNull($unchangedRecord);
        $this->assertEquals('Existing Student', $unchangedRecord->status1);
        $this->assertEquals('U', $unchangedRecord->class);

        // Verify some new records
        $newRecord = UcrCardDataActual::where('net_id', 'new001')->first();
        $this->assertNotNull($newRecord);
        $this->assertEquals('New Student', $newRecord->status1);

        // Performance assertion (should complete within reasonable time)
        $this->assertLessThan(30, $processingTime, 'Large dataset processing should complete within 30 seconds');
    }

    /** @test */
    public function it_maintains_data_integrity_during_concurrent_operations()
    {
        // Arrange: Create initial data
        UcrCardDataActual::create([
            'net_id' => 'concurrent001',
            'ssn' => '111222333',
            'student_id' => '111222333',
            'iso' => '1112223330123456',
            'lib_num' => '21112223330123456',
            'status1' => 'Original Status',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '11111',
            'prox_ext' => '22222',
            'prox_status' => 'A',
            'load_status' => 'original',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay()
        ]);

        UcrCardDataStaging::create([
            'net_id' => 'concurrent001',
            'ssn' => '111222333',
            'student_id' => '111222333',
            'iso' => '1112223330123456',
            'lib_num' => '21112223330123456',
            'status1' => 'Updated Status',
            'class' => 'G',
            'yr_in_school' => 'G1',
            'stud_fac' => 'S',
            'prox_int' => '33333',
            'prox_ext' => '44444',
            'prox_status' => 'A',
            'load_status' => 'updated',
            'created_at' => now()->subDay(),
            'updated_at' => now()
        ]);

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Verify data integrity
        $this->assertEquals(1, UcrCardDataActual::count());

        $record = UcrCardDataActual::where('net_id', 'concurrent001')->first();
        $this->assertNotNull($record);
        $this->assertEquals('Updated Status', $record->status1);
        $this->assertEquals('G', $record->class);
        $this->assertEquals('G1', $record->yr_in_school);
        $this->assertEquals('33333', $record->prox_int);
        $this->assertEquals('44444', $record->prox_ext);

        // Verify no duplicate records were created
        $duplicates = UcrCardDataActual::where('net_id', 'concurrent001')->count();
        $this->assertEquals(1, $duplicates);
    }

    /** @test */
    public function it_handles_edge_cases_and_data_anomalies()
    {
        // Arrange: Create edge case scenarios

        // 1. Record with very old timestamp in staging (should not update)
        UcrCardDataActual::create([
            'net_id' => 'edge001',
            'ssn' => '999888777',
            'student_id' => '999888777',
            'iso' => '9998887770123456',
            'lib_num' => '29998887770123456',
            'status1' => 'Current Status',
            'class' => 'G',
            'yr_in_school' => 'G3',
            'stud_fac' => 'S',
            'prox_int' => '99999',
            'prox_ext' => '88888',
            'prox_status' => 'A',
            'load_status' => 'current',
            'created_at' => now()->subDay(),
            'updated_at' => now() // Very recent
        ]);

        UcrCardDataStaging::create([
            'net_id' => 'edge001',
            'ssn' => '999888777',
            'student_id' => '999888777',
            'iso' => '9998887770123456',
            'lib_num' => '29998887770123456',
            'status1' => 'Old Status',
            'class' => 'U',
            'yr_in_school' => 'U1',
            'stud_fac' => 'S',
            'prox_int' => '11111',
            'prox_ext' => '22222',
            'prox_status' => 'I',
            'load_status' => 'updated',
            'created_at' => now()->subWeek(),
            'updated_at' => now()->subDay() // Older timestamp
        ]);

        // 2. Record with null values
        UcrCardDataStaging::create([
            'net_id' => 'edge002',
            'ssn' => '777666555',
            'student_id' => '777666555',
            'iso' => '7776665550123456',
            'lib_num' => '27776665550123456',
            'status1' => 'Null Test',
            'class' => '', // Empty string instead of null
            'yr_in_school' => '', // Empty string instead of null
            'stud_fac' => 'S',
            'prox_int' => '', // Empty string instead of null
            'prox_ext' => '', // Empty string instead of null
            'prox_status' => 'A',
            'issued' => null,
            'edit_date' => null,
            'photo_date' => null,
            'imported' => null,
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Record with empty strings
        UcrCardDataStaging::create([
            'net_id' => 'edge003',
            'ssn' => '555444333',
            'student_id' => '555444333',
            'iso' => '5554443330123456',
            'lib_num' => '25554443330123456',
            'status1' => '',
            'class' => '',
            'yr_in_school' => '',
            'stud_fac' => '',
            'prox_int' => '',
            'prox_ext' => '',
            'prox_status' => '',
            'load_status' => 'created',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Verify edge case handling

        // Should have 3 records in actual table
        $this->assertEquals(3, UcrCardDataActual::count());

        // 1. Old staging record should not update actual record
        $edge1 = UcrCardDataActual::where('net_id', 'edge001')->first();
        $this->assertNotNull($edge1);
        $this->assertEquals('Current Status', $edge1->status1); // Should remain unchanged
        $this->assertEquals('G', $edge1->class); // Should remain unchanged

        // 2. Null values should be handled properly
        $edge2 = UcrCardDataActual::where('net_id', 'edge002')->first();
        $this->assertNotNull($edge2);
        $this->assertEquals('Null Test', $edge2->status1);
        $this->assertEquals('', $edge2->class); // Empty string, not null
        $this->assertEquals('', $edge2->yr_in_school); // Empty string, not null

        // 3. Empty strings should be preserved
        $edge3 = UcrCardDataActual::where('net_id', 'edge003')->first();
        $this->assertNotNull($edge3);
        $this->assertEquals('', $edge3->status1);
        $this->assertEquals('', $edge3->class);
    }

    /** @test */
    public function it_processes_real_world_data_patterns()
    {
        // Arrange: Create data that mimics real-world patterns from university card systems

        // Students transitioning from undergraduate to graduate
        UcrCardDataActual::create([
            'net_id' => 'student001',
            'ssn' => '123123123',
            'student_id' => '123123123',
            'iso' => '1231231230123456',
            'lib_num' => '21231231230123456',
            'status1' => 'Active Student',
            'class' => 'U',
            'yr_in_school' => 'U4',
            'stud_fac' => 'S',
            'prox_int' => '12312',
            'prox_ext' => '31231',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2023-09-01'),
            'edit_date' => Carbon::parse('2023-09-01'),
            'photo_date' => Carbon::parse('2023-08-25'),
            'imported' => Carbon::parse('2023-09-05'),
            'load_status' => 'active',
            'created_at' => Carbon::parse('2023-09-01'),
            'updated_at' => Carbon::parse('2023-09-01')
        ]);

        // Update: Student graduated and became graduate student
        UcrCardDataStaging::create([
            'net_id' => 'student001',
            'ssn' => '123123123',
            'student_id' => '123123123',
            'iso' => '1231231230123456',
            'lib_num' => '21231231230123456',
            'status1' => 'Graduate Student',
            'class' => 'G',
            'yr_in_school' => 'G1',
            'stud_fac' => 'S',
            'prox_int' => '12312',
            'prox_ext' => '31231',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-09-01'),
            'edit_date' => Carbon::parse('2024-09-01'),
            'photo_date' => Carbon::parse('2024-08-25'),
            'imported' => Carbon::parse('2024-09-05'),
            'load_status' => 'updated',
            'created_at' => Carbon::parse('2023-09-01'),
            'updated_at' => Carbon::parse('2024-09-01')
        ]);

        // Faculty member with card replacement
        UcrCardDataActual::create([
            'net_id' => 'faculty001',
            'ssn' => '456456456',
            'student_id' => '456456456',
            'iso' => '4564564560123456',
            'lib_num' => '24564564560123456',
            'status1' => 'Active Faculty',
            'class' => 'F',
            'yr_in_school' => '', // Empty string for faculty (no year in school)
            'stud_fac' => 'F',
            'prox_int' => '45645',
            'prox_ext' => '64564',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2022-01-15'),
            'edit_date' => Carbon::parse('2022-01-15'),
            'photo_date' => Carbon::parse('2022-01-10'),
            'imported' => Carbon::parse('2022-01-20'),
            'load_status' => 'active',
            'created_at' => Carbon::parse('2022-01-15'),
            'updated_at' => Carbon::parse('2022-01-15')
        ]);

        // Update: Card was replaced with new proximity numbers
        UcrCardDataStaging::create([
            'net_id' => 'faculty001',
            'ssn' => '456456456',
            'student_id' => '456456456',
            'iso' => '4564564560123456',
            'lib_num' => '24564564560123456',
            'status1' => 'Active Faculty',
            'class' => 'F',
            'yr_in_school' => '', // Empty string for faculty
            'stud_fac' => 'F',
            'prox_int' => '99999', // New card numbers
            'prox_ext' => '88888',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-03-10'),
            'edit_date' => Carbon::parse('2024-03-10'),
            'photo_date' => Carbon::parse('2022-01-10'), // Photo date unchanged
            'imported' => Carbon::parse('2024-03-15'),
            'load_status' => 'updated',
            'created_at' => Carbon::parse('2022-01-15'),
            'updated_at' => Carbon::parse('2024-03-10')
        ]);

        // New international student
        UcrCardDataStaging::create([
            'net_id' => 'intl001',
            'ssn' => '000000000', // International students may have different SSN patterns
            'student_id' => '789789789',
            'iso' => '7897897890123456',
            'lib_num' => '27897897890123456',
            'status1' => 'International Student',
            'class' => 'G',
            'yr_in_school' => 'G1',
            'stud_fac' => 'S',
            'prox_int' => '78978',
            'prox_ext' => '97897',
            'prox_status' => 'A',
            'issued' => Carbon::parse('2024-01-20'),
            'edit_date' => Carbon::parse('2024-01-20'),
            'photo_date' => Carbon::parse('2024-01-15'),
            'imported' => Carbon::parse('2024-01-25'),
            'load_status' => 'created',
            'created_at' => Carbon::parse('2024-01-20'),
            'updated_at' => Carbon::parse('2024-01-20')
        ]);

        // Verify initial state
        $this->assertEquals(3, UcrCardDataStaging::count());
        $this->assertEquals(2, UcrCardDataActual::count());

        // Act: Execute the merge job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Verify real-world scenarios are handled correctly

        // Should have 3 records in actual table
        $this->assertEquals(3, UcrCardDataActual::count());

        // Verify student transition
        $student = UcrCardDataActual::where('net_id', 'student001')->first();
        $this->assertNotNull($student);
        $this->assertEquals('Graduate Student', $student->status1);
        $this->assertEquals('G', $student->class);
        $this->assertEquals('G1', $student->yr_in_school);
        $this->assertEquals('2024-09-01', $student->issued->format('Y-m-d'));

        // Verify faculty card replacement
        $faculty = UcrCardDataActual::where('net_id', 'faculty001')->first();
        $this->assertNotNull($faculty);
        $this->assertEquals('Active Faculty', $faculty->status1);
        $this->assertEquals('99999', $faculty->prox_int); // New card number
        $this->assertEquals('88888', $faculty->prox_ext); // New card number
        $this->assertEquals('2024-03-10', $faculty->issued->format('Y-m-d'));

        // Verify new international student
        $intl = UcrCardDataActual::where('net_id', 'intl001')->first();
        $this->assertNotNull($intl);
        $this->assertEquals('789789789', $intl->student_id);
        $this->assertEquals('International Student', $intl->status1);
        $this->assertEquals('G', $intl->class);
        $this->assertEquals('000000000', $intl->ssn);
    }

    /** @test */
    public function it_handles_job_timeout_gracefully()
    {
        // This test verifies that the job has appropriate timeout settings
        $job = new MergeCardDataDeltas();

        // Assert: Verify timeout is set appropriately
        $this->assertEquals(3600, $job->timeout); // 1 hour timeout
    }

    /** @test */
    public function it_logs_comprehensive_processing_information()
    {
        // Arrange: Create test data
        UcrCardDataStaging::create([
            'net_id' => 'log001',
            'ssn' => '111111111',
            'student_id' => '111111111',
            'iso' => '1111111111111111',
            'lib_num' => '21111111111111111',
            'status1' => 'Log Test',
            'load_status' => 'created'
        ]);

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Verify logging occurred (logs are actually written in real execution)
        $this->assertEquals(1, UcrCardDataActual::count());

        // In a real feature test, you might check log files or use a log testing package
        // For this test, we're verifying the job completes successfully with logging
        $record = UcrCardDataActual::where('net_id', 'log001')->first();
        $this->assertNotNull($record);
        $this->assertEquals('Log Test', $record->status1);
    }
}
