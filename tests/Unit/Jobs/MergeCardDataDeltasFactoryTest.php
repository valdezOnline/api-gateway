<?php

namespace Tests\Unit\Jobs;

use App\Jobs\MergeCardDataDeltas;
use App\Models\UcrCardDataStaging;
use App\Models\UcrCardDataActual;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Mockery;
use PHPUnitrameworkattributestest;

class MergeCardDataDeltasFactoryTest extends TestCase
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
    public function it_processes_student_lifecycle_changes_using_factories()
    {
        // Arrange: Create an undergraduate student in actual table
        $actualStudent = UcrCardDataActual::factory()
            ->student()
            ->older()
            ->create([
                'net_id' => 'student001',
                'status1' => 'Active Student',
                'class' => 'U',
                'yr_in_school' => 'U4'
            ]);

        // Create updated record in staging (student graduated to graduate program)
        $stagingStudent = UcrCardDataStaging::factory()
            ->student()
            ->updated()
            ->newer()
            ->create([
                'net_id' => 'student001',
                'ssn' => $actualStudent->ssn,
                'student_id' => $actualStudent->student_id,
                'status1' => 'Graduate Student',
                'class' => 'G',
                'yr_in_school' => 'G1'
            ]);

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Student record should be updated
        $this->assertEquals(1, UcrCardDataActual::count());

        $updatedStudent = UcrCardDataActual::where('net_id', 'student001')->first();
        $this->assertEquals('Graduate Student', $updatedStudent->status1);
        $this->assertEquals('G', $updatedStudent->class);
        $this->assertEquals('G1', $updatedStudent->yr_in_school);
    }
    #[Test]
    public function it_handles_faculty_card_replacements_using_factories()
    {
        // Arrange: Create faculty member with existing card
        $actualFaculty = UcrCardDataActual::factory()
            ->faculty()
            ->active()
            ->older()
            ->create([
                'net_id' => 'prof001',
                'prox_int' => '11111',
                'prox_ext' => '22222'
            ]);

        // Create staging record with new card numbers (card replacement)
        $stagingFaculty = UcrCardDataStaging::factory()
            ->faculty()
            ->updated()
            ->newer()
            ->create([
                'net_id' => 'prof001',
                'ssn' => $actualFaculty->ssn,
                'student_id' => $actualFaculty->student_id,
                'prox_int' => '99999', // New card numbers
                'prox_ext' => '88888'
            ]);

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Faculty record should have new card numbers
        $this->assertEquals(1, UcrCardDataActual::count());

        $updatedFaculty = UcrCardDataActual::where('net_id', 'prof001')->first();
        $this->assertEquals('99999', $updatedFaculty->prox_int);
        $this->assertEquals('88888', $updatedFaculty->prox_ext);
    }
    #[Test]
    public function it_processes_bulk_new_students_using_factories()
    {
        // Arrange: Create multiple new students in staging
        $newStudents = UcrCardDataStaging::factory()
            ->student()
            ->created()
            ->count(10)
            ->create();

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: All new students should be inserted
        $this->assertEquals(10, UcrCardDataActual::count());

        // Verify all students are present
        foreach ($newStudents as $student) {
            $actualRecord = UcrCardDataActual::where('net_id', $student->net_id)->first();
            $this->assertNotNull($actualRecord);
            $this->assertEquals($student->ssn, $actualRecord->ssn);
            $this->assertEquals($student->status1, $actualRecord->status1);
        }
    }
    #[Test]
    public function it_handles_mixed_operations_with_factories()
    {
        // Arrange: Create existing records in actual table
        $existingStudents = UcrCardDataActual::factory()
            ->student()
            ->older()
            ->count(5)
            ->create();

        $existingFaculty = UcrCardDataActual::factory()
            ->faculty()
            ->older()
            ->count(3)
            ->create();

        // Create staging records for updates (first 3 students)
        foreach ($existingStudents->take(3) as $student) {
            UcrCardDataStaging::factory()
                ->student()
                ->updated()
                ->newer()
                ->create([
                    'net_id' => $student->net_id,
                    'ssn' => $student->ssn,
                    'student_id' => $student->student_id,
                    'status1' => 'Updated Student Status',
                    'class' => 'G' // Changed from U to G
                ]);
        }

        // Create new records in staging
        $newRecords = UcrCardDataStaging::factory()
            ->created()
            ->count(4)
            ->create();

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Should have 12 records total (8 existing + 4 new)
        $this->assertEquals(12, UcrCardDataActual::count());

        // Verify updates were applied
        $updatedCount = UcrCardDataActual::where('status1', 'Updated Student Status')->count();
        $this->assertEquals(3, $updatedCount);

        // Verify new records were inserted
        foreach ($newRecords as $newRecord) {
            $actualRecord = UcrCardDataActual::where('net_id', $newRecord->net_id)->first();
            $this->assertNotNull($actualRecord);
        }
    }
    #[Test]
    public function it_respects_timestamp_precedence_with_factories()
    {
        // Arrange: Create newer record in actual table
        $actualRecord = UcrCardDataActual::factory()
            ->newer()
            ->create([
                'net_id' => 'test001',
                'status1' => 'Newer Status'
            ]);

        // Create older record in staging (should not update)
        $stagingRecord = UcrCardDataStaging::factory()
            ->updated()
            ->older()
            ->create([
                'net_id' => 'test001',
                'ssn' => $actualRecord->ssn,
                'student_id' => $actualRecord->student_id,
                'status1' => 'Older Status'
            ]);

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Record should not be updated (staging is older)
        $this->assertEquals(1, UcrCardDataActual::count());

        $record = UcrCardDataActual::where('net_id', 'test001')->first();
        $this->assertEquals('Newer Status', $record->status1);
    }
    #[Test]
    public function it_processes_international_students_correctly()
    {
        // Arrange: Create international student records
        $intlStudents = UcrCardDataStaging::factory()
            ->student()
            ->created()
            ->count(3)
            ->create([
                'status1' => 'International Student',
                'ssn' => '000000000' // Common pattern for international students
            ]);

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: International students should be processed correctly
        $this->assertEquals(3, UcrCardDataActual::count());

        $intlCount = UcrCardDataActual::where('status1', 'International Student')->count();
        $this->assertEquals(3, $intlCount);

        $ssnCount = UcrCardDataActual::where('ssn', '000000000')->count();
        $this->assertEquals(3, $ssnCount);
    }
    #[Test]
    public function it_handles_status_transitions_realistically()
    {
        // Arrange: Create various realistic status transitions

        // Student becomes inactive
        $inactiveStudent = UcrCardDataActual::factory()
            ->student()
            ->active()
            ->older()
            ->create([
                'net_id' => 'inactive001',
                'status1' => 'Active Student',
                'prox_status' => 'A'
            ]);

        UcrCardDataStaging::factory()
            ->updated()
            ->newer()
            ->create([
                'net_id' => 'inactive001',
                'ssn' => $inactiveStudent->ssn,
                'student_id' => $inactiveStudent->student_id,
                'status1' => 'Inactive Student',
                'prox_status' => 'I'
            ]);

        // Faculty retires
        $retiredFaculty = UcrCardDataActual::factory()
            ->faculty()
            ->active()
            ->older()
            ->create([
                'net_id' => 'retired001',
                'status1' => 'Active Faculty'
            ]);

        UcrCardDataStaging::factory()
            ->updated()
            ->newer()
            ->create([
                'net_id' => 'retired001',
                'ssn' => $retiredFaculty->ssn,
                'student_id' => $retiredFaculty->student_id,
                'status1' => 'Retired Faculty',
                'prox_status' => 'I'
            ]);

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Status transitions should be applied
        $this->assertEquals(2, UcrCardDataActual::count());

        $inactiveRecord = UcrCardDataActual::where('net_id', 'inactive001')->first();
        $this->assertEquals('Inactive Student', $inactiveRecord->status1);
        $this->assertEquals('I', $inactiveRecord->prox_status);

        $retiredRecord = UcrCardDataActual::where('net_id', 'retired001')->first();
        $this->assertEquals('Retired Faculty', $retiredRecord->status1);
        $this->assertEquals('I', $retiredRecord->prox_status);
    }
    #[Test]
    public function it_preserves_data_integrity_during_complex_updates()
    {
        // Arrange: Create complex scenario with overlapping identifiers
        $baseData = [
            'ssn' => '123456789',
            'student_id' => '123456789'
        ];

        // Existing record
        $existingRecord = UcrCardDataActual::factory()
            ->older()
            ->create(array_merge($baseData, [
                'net_id' => 'complex001',
                'status1' => 'Original Status',
                'class' => 'U',
                'prox_int' => '11111'
            ]));

        // Staging record with same identifiers but different net_id (edge case)
        $stagingRecord = UcrCardDataStaging::factory()
            ->updated()
            ->newer()
            ->create(array_merge($baseData, [
                'net_id' => 'complex001', // Same net_id
                'status1' => 'Updated Status',
                'class' => 'G',
                'prox_int' => '22222'
            ]));

        Log::shouldReceive('info')->withAnyArgs()->zeroOrMoreTimes();

        // Act: Execute the job
        $job = new MergeCardDataDeltas();
        $job->handle();

        // Assert: Data integrity should be maintained
        $this->assertEquals(1, UcrCardDataActual::count());

        $record = UcrCardDataActual::where('net_id', 'complex001')->first();
        $this->assertEquals('Updated Status', $record->status1);
        $this->assertEquals('G', $record->class);
        $this->assertEquals('22222', $record->prox_int);
        $this->assertEquals($baseData['ssn'], $record->ssn);
        $this->assertEquals($baseData['student_id'], $record->student_id);
    }
}
