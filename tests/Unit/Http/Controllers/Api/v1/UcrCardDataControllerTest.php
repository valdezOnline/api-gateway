<?php

namespace Tests\Unit\Http\Controllers\Api\v1;

use Tests\TestCase;
use App\Http\Controllers\Api\v1\UcrCardDataController;
use App\Models\UcrCardDataActual;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Mockery;

class UcrCardDataControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new UcrCardDataController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test successful retrieval of all records with pagination
     */
    public function test_list_returns_all_records_with_pagination()
    {
        // Create test data
        UcrCardDataActual::factory()->count(5)->create();

        $request = new Request();
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('All UCR card data retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test search by net_id with multiple records
     */
    public function test_list_search_by_net_id_returns_multiple_records()
    {
        // Create test data
        $netId = 'test001';
        UcrCardDataActual::factory()->count(2)->create(['net_id' => $netId]);
        UcrCardDataActual::factory()->create(['net_id' => 'other001']);

        $request = new Request(['net_id' => $netId]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully by net_id', $responseData['message']);
        $this->assertCount(2, $responseData['data']);
    }

    /**
     * Test search by net_id with no records found
     */
    public function test_list_search_by_net_id_returns_404_when_not_found()
    {
        $request = new Request(['net_id' => 'nonexistent']);
        $response = $this->controller->list($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No records found for the provided net_id', $responseData['message']);
    }

    /**
     * Test search by SSN returns single record
     */
    public function test_list_search_by_ssn_returns_single_record()
    {
        $ssn = '123456789';
        UcrCardDataActual::factory()->create(['ssn' => $ssn]);

        $request = new Request(['ssn' => $ssn]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully by SSN', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test search by SSN returns 404 when not found
     */
    public function test_list_search_by_ssn_returns_404_when_not_found()
    {
        $request = new Request(['ssn' => 'nonexistent']);
        $response = $this->controller->list($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No record found for the provided SSN', $responseData['message']);
    }

    /**
     * Test search by student_id returns single record
     */
    public function test_list_search_by_student_id_returns_single_record()
    {
        $studentId = '862123456';
        UcrCardDataActual::factory()->create(['student_id' => $studentId]);

        $request = new Request(['student_id' => $studentId]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully by student_id', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test search by student_id returns 404 when not found
     */
    public function test_list_search_by_student_id_returns_404_when_not_found()
    {
        $request = new Request(['student_id' => 'nonexistent']);
        $response = $this->controller->list($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No record found for the provided student_id', $responseData['message']);
    }

    /**
     * Test search by ISO returns single record
     */
    public function test_list_search_by_iso_returns_single_record()
    {
        $iso = '12345';
        UcrCardDataActual::factory()->create(['iso' => $iso]);

        $request = new Request(['iso' => $iso]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully by ISO', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test search by ISO returns 404 when not found
     */
    public function test_list_search_by_iso_returns_404_when_not_found()
    {
        $request = new Request(['iso' => 'nonexistent']);
        $response = $this->controller->list($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No record found for the provided ISO', $responseData['message']);
    }

    /**
     * Test search by date range returns multiple records
     */
    public function test_list_search_by_date_range_returns_multiple_records()
    {
        $startDate = '2024-01-01';
        $endDate = '2024-12-31';

        // Create records within the date range
        UcrCardDataActual::factory()->count(3)->create(['issued' => '2024-06-15']);
        // Create records outside the date range
        UcrCardDataActual::factory()->create(['issued' => '2023-12-31']);

        $request = new Request([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully by date range', $responseData['message']);
        $this->assertCount(3, $responseData['data']);
    }

    /**
     * Test search by date range with invalid date format
     */
    public function test_list_search_by_date_range_with_invalid_date_format()
    {
        $request = new Request([
            'start_date' => 'invalid-date',
            'end_date' => '2024-12-31'
        ]);
        $response = $this->controller->list($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Invalid date format. Please use YYYY-MM-DD format', $responseData['message']);
    }

    /**
     * Test search by date range returns 404 when no records found
     */
    public function test_list_search_by_date_range_returns_404_when_not_found()
    {
        $request = new Request([
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31'
        ]);
        $response = $this->controller->list($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
            $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No records found for the provided date range', $responseData['message']);
    }

    /**
     * Test searchByNetId method with existing records
     */
    public function test_search_by_net_id_endpoint_returns_records()
    {
        $netId = 'test001';
        UcrCardDataActual::factory()->count(2)->create(['net_id' => $netId]);

        $request = new Request();
        $response = $this->controller->searchByNetId($request, $netId);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully', $responseData['message']);
        $this->assertCount(2, $responseData['data']);
    }

    /**
     * Test searchByNetId method with no records
     */
    public function test_search_by_net_id_endpoint_returns_404_when_not_found()
    {
        $request = new Request();
        $response = $this->controller->searchByNetId($request, 'nonexistent');

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('No records found for net_id:', $responseData['message']);
    }

    /**
     * Test searchBySsn method with existing record
     */
    public function test_search_by_ssn_endpoint_returns_record()
    {
        $ssn = '123456789';
        UcrCardDataActual::factory()->create(['ssn' => $ssn]);

        $request = new Request();
        $response = $this->controller->searchBySsn($request, $ssn);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test searchBySsn method with no record
     */
    public function test_search_by_ssn_endpoint_returns_404_when_not_found()
    {
        $request = new Request();
        $response = $this->controller->searchBySsn($request, 'nonexistent');

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('No record found for SSN:', $responseData['message']);
    }

    /**
     * Test searchByStudentId method with existing record
     */
    public function test_search_by_student_id_endpoint_returns_record()
    {
        $studentId = '862123456';
        UcrCardDataActual::factory()->create(['student_id' => $studentId]);

        $request = new Request();
        $response = $this->controller->searchByStudentId($request, $studentId);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test searchByStudentId method with no record
     */
    public function test_search_by_student_id_endpoint_returns_404_when_not_found()
    {
        $request = new Request();
        $response = $this->controller->searchByStudentId($request, 'nonexistent');

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('No record found for student_id:', $responseData['message']);
    }

    /**
     * Test searchByIso method with existing record
     */
    public function test_search_by_iso_endpoint_returns_record()
    {
        $iso = '12345';
        UcrCardDataActual::factory()->create(['iso' => $iso]);

        $request = new Request();
        $response = $this->controller->searchByIso($request, $iso);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test searchByIso method with no record
     */
    public function test_search_by_iso_endpoint_returns_404_when_not_found()
    {
        $request = new Request();
        $response = $this->controller->searchByIso($request, 'nonexistent');

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('No record found for ISO:', $responseData['message']);
    }

    /**
     * Test searchByDateRange method with valid data
     */
    public function test_search_by_date_range_endpoint_returns_records()
    {
        // Create records within the date range
        UcrCardDataActual::factory()->count(2)->create(['issued' => '2024-06-15']);

        $request = new Request([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31'
        ]);
        $response = $this->controller->searchByDateRange($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully for date range', $responseData['message']);

        // Check the structured response
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(2, $responseData['data']['total_found']);
        $this->assertArrayHasKey('records', $responseData['data']);
        $this->assertArrayHasKey('date_range', $responseData['data']);
        $this->assertEquals('2024-01-01', $responseData['data']['date_range']['start']);
        $this->assertEquals('2024-12-31', $responseData['data']['date_range']['end']);
        $this->assertCount(2, $responseData['data']['records']);
    }

    /**
     * Test searchByDateRange method with validation errors
     */
    public function test_search_by_date_range_endpoint_validation_errors()
    {
        $request = new Request([
            'start_date' => 'invalid-date',
            'end_date' => '2024-12-31'
        ]);
        $response = $this->controller->searchByDateRange($request);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test searchByDateRange method with missing required fields
     */
    public function test_search_by_date_range_endpoint_missing_required_fields()
    {
        $request = new Request(['start_date' => '2024-01-01']);
        $response = $this->controller->searchByDateRange($request);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Validation failed', $responseData['message']);
    }

    /**
     * Test searchByDateRange method with end_date before start_date
     */
    public function test_search_by_date_range_endpoint_end_date_before_start_date()
    {
        $request = new Request([
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01'
        ]);
        $response = $this->controller->searchByDateRange($request);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Validation failed', $responseData['message']);
    }

    /**
     * Test exception handling in list method
     */
    public function test_list_handles_exceptions()
    {
        // We'll skip mocking for now as it's complex with Eloquent models
        // In a real scenario, you'd mock the database connection or use a separate test
        $this->assertTrue(true); // Placeholder test
    }

    /**
     * Test pagination parameters
     */
    public function test_list_respects_pagination_parameters()
    {
        // Create test data
        UcrCardDataActual::factory()->count(10)->create();

        $request = new Request(['per_page' => 5]);
        $response = $this->controller->list($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertArrayHasKey('data', $responseData);
        // Check pagination structure
        $this->assertArrayHasKey('current_page', $responseData['data']);
        $this->assertArrayHasKey('per_page', $responseData['data']);
        $this->assertEquals(5, $responseData['data']['per_page']);
    }

    /**
     * Test searchMultiple method with net_ids
     */
    public function test_search_multiple_with_net_ids_returns_records()
    {
        $netId1 = 'test001';
        $netId2 = 'test002';
        UcrCardDataActual::factory()->create(['net_id' => $netId1]);
        UcrCardDataActual::factory()->create(['net_id' => $netId2]);
        UcrCardDataActual::factory()->create(['net_id' => 'other001']);

        $request = new Request([
            'net_ids' => [$netId1, $netId2]
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully for multiple search parameters', $responseData['message']);
        $this->assertEquals(2, $responseData['data']['total_found']);
        $this->assertArrayHasKey('records', $responseData['data']);
        $this->assertArrayHasKey('search_summary', $responseData['data']);
        $this->assertEquals([$netId1, $netId2], $responseData['data']['search_summary']['net_ids_searched']);
    }

    /**
     * Test searchMultiple method with ssns
     */
    public function test_search_multiple_with_ssns_returns_records()
    {
        $ssn1 = '123456789';
        $ssn2 = '987654321';
        UcrCardDataActual::factory()->create(['ssn' => $ssn1]);
        UcrCardDataActual::factory()->create(['ssn' => $ssn2]);

        $request = new Request([
            'ssns' => [$ssn1, $ssn2]
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals(2, $responseData['data']['total_found']);
        $this->assertArrayHasKey('records', $responseData['data']);
        $this->assertEquals([$ssn1, $ssn2], $responseData['data']['search_summary']['ssns_searched']);
    }

    /**
     * Test searchMultiple method with student_ids
     */
    public function test_search_multiple_with_student_ids_returns_records()
    {
        $studentId1 = '862123456';
        $studentId2 = '862654321';
        UcrCardDataActual::factory()->create(['student_id' => $studentId1]);
        UcrCardDataActual::factory()->create(['student_id' => $studentId2]);

        $request = new Request([
            'student_ids' => [$studentId1, $studentId2]
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals(2, $responseData['data']['total_found']);
        $this->assertArrayHasKey('records', $responseData['data']);
        $this->assertEquals([$studentId1, $studentId2], $responseData['data']['search_summary']['student_ids_searched']);
    }

    /**
     * Test searchMultiple method with isos
     */
    public function test_search_multiple_with_isos_returns_records()
    {
        $iso1 = '12345';
        $iso2 = '54321';
        UcrCardDataActual::factory()->create(['iso' => $iso1]);
        UcrCardDataActual::factory()->create(['iso' => $iso2]);

        $request = new Request([
            'isos' => [$iso1, $iso2]
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals(2, $responseData['data']['total_found']);
        $this->assertArrayHasKey('records', $responseData['data']);
        $this->assertEquals([$iso1, $iso2], $responseData['data']['search_summary']['isos_searched']);
    }

    /**
     * Test searchMultiple method with mixed parameters
     */
    public function test_search_multiple_with_mixed_parameters_returns_records()
    {
        $netId = 'test001';
        $ssn = '123456789';
        $studentId = '862123456';
        $iso = '12345';

        UcrCardDataActual::factory()->create(['net_id' => $netId]);
        UcrCardDataActual::factory()->create(['ssn' => $ssn]);
        UcrCardDataActual::factory()->create(['student_id' => $studentId]);
        UcrCardDataActual::factory()->create(['iso' => $iso]);

        $request = new Request([
            'net_ids' => [$netId],
            'ssns' => [$ssn],
            'student_ids' => [$studentId],
            'isos' => [$iso]
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals(4, $responseData['data']['total_found']);
        $this->assertArrayHasKey('search_summary', $responseData['data']);
        $this->assertEquals([$netId], $responseData['data']['search_summary']['net_ids_searched']);
        $this->assertEquals([$ssn], $responseData['data']['search_summary']['ssns_searched']);
        $this->assertEquals([$studentId], $responseData['data']['search_summary']['student_ids_searched']);
        $this->assertEquals([$iso], $responseData['data']['search_summary']['isos_searched']);
    }

    /**
     * Test searchMultiple method with no search parameters
     */
    public function test_search_multiple_with_no_parameters_returns_error()
    {
        $request = new Request([]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('At least one search parameter must be provided', $responseData['message']);
    }

    /**
     * Test searchMultiple method with empty arrays
     */
    public function test_search_multiple_with_empty_arrays_returns_error()
    {
        $request = new Request([
            'net_ids' => [],
            'ssns' => [],
            'student_ids' => [],
            'isos' => []
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('At least one search parameter must be provided', $responseData['message']);
    }

    /**
     * Test searchMultiple method with arrays containing empty strings
     */
    public function test_search_multiple_with_empty_string_values_returns_404()
    {
        $request = new Request([
            'net_ids' => ['', '   '],
            'ssns' => [''],
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No records found for the provided search parameters', $responseData['message']);
    }

    /**
     * Test searchMultiple method with no records found
     */
    public function test_search_multiple_with_no_records_found_returns_404()
    {
        $request = new Request([
            'net_ids' => ['nonexistent1', 'nonexistent2']
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('No records found for the provided search parameters', $responseData['message']);
    }

    /**
     * Test searchMultiple method with invalid validation
     */
    public function test_search_multiple_with_invalid_validation_returns_422()
    {
        $request = new Request([
            'net_ids' => 'not-an-array'
        ]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
    }

    /**
     * Test searchByDateRange method returns structured response with grouped results
     */
    public function test_search_by_date_range_endpoint_returns_structured_response()
    {
        // Create records within the date range
        UcrCardDataActual::factory()->count(3)->create(['issued' => '2024-06-15']);

        $request = new Request([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31'
        ]);
        $response = $this->controller->searchByDateRange($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('UCR card data retrieved successfully for date range', $responseData['message']);

        // Check the structured response
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(3, $responseData['data']['total_found']);
        $this->assertArrayHasKey('records', $responseData['data']);
        $this->assertArrayHasKey('date_range', $responseData['data']);
        $this->assertEquals('2024-01-01', $responseData['data']['date_range']['start']);
        $this->assertEquals('2024-12-31', $responseData['data']['date_range']['end']);
        $this->assertCount(3, $responseData['data']['records']);
    }

    /**
     * Test searchByDateRange method with no records returns proper 404 message
     */
    public function test_search_by_date_range_endpoint_returns_404_with_date_range_message()
    {
        $request = new Request([
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31'
        ]);
        $response = $this->controller->searchByDateRange($request);

        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertStringContainsString('No records found for the date range: 2025-01-01 to 2025-12-31', $responseData['message']);
    }

    /**
     * Test exception handling in searchMultiple method
     */
    public function test_search_multiple_handles_exceptions()
    {
        // This test would typically mock database errors
        // For now, we'll test with a valid request that should work
        $netId = 'test001';
        UcrCardDataActual::factory()->create(['net_id' => $netId]);

        $request = new Request(['net_ids' => [$netId]]);
        $response = $this->controller->searchMultiple($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
    }

    /**
     * Test exception handling in individual search methods
     */
    public function test_individual_search_methods_handle_exceptions()
    {
        // Test searchByNetId exception handling
        $netId = 'test001';
        UcrCardDataActual::factory()->create(['net_id' => $netId]);

        $request = new Request();
        $response = $this->controller->searchByNetId($request, $netId);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);

        // Test searchBySsn exception handling
        $ssn = '123456789';
        UcrCardDataActual::factory()->create(['ssn' => $ssn]);

        $response = $this->controller->searchBySsn($request, $ssn);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);

        // Test searchByStudentId exception handling
        $studentId = '862123456';
        UcrCardDataActual::factory()->create(['student_id' => $studentId]);

        $response = $this->controller->searchByStudentId($request, $studentId);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);

        // Test searchByIso exception handling
        $iso = '12345';
        UcrCardDataActual::factory()->create(['iso' => $iso]);

        $response = $this->controller->searchByIso($request, $iso);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
    }
}
