<?php

namespace Tests\Feature\Http\Controllers\Api\v1;

use Tests\TestCase;
use App\Models\UcrCardDataActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class UcrCardDataControllerFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    /**
     * Test list endpoint returns all records with pagination
     */
    public function test_list_endpoint_returns_all_records_with_pagination()
    {
        Sanctum::actingAs($this->user);

        // Create test data
        UcrCardDataActual::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
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
                        ]
                    ],
                    'per_page',
                    'total'
                ]
            ])
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'All UCR card data retrieved successfully');
    }

    /**
     * Test list endpoint requires authentication
     */
    public function test_list_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(401);
    }

    /**
     * Test search by net_id query parameter
     */
    public function test_list_endpoint_search_by_net_id()
    {
        Sanctum::actingAs($this->user);

        $netId = 'test001';
        UcrCardDataActual::factory()->count(2)->create(['net_id' => $netId]);
        UcrCardDataActual::factory()->create(['net_id' => 'other001']);

        $response = $this->getJson('/api/v1/ucr-card-data/list?net_id=' . $netId);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully by net_id')
            ->assertJsonCount(2, 'data');

        // Verify all returned records have the correct net_id
        $data = $response->json('data');
        foreach ($data as $record) {
            $this->assertEquals($netId, $record['net_id']);
        }
    }

    /**
     * Test search by SSN query parameter
     */
    public function test_list_endpoint_search_by_ssn()
    {
        Sanctum::actingAs($this->user);

        $ssn = '123456789';
        $record = UcrCardDataActual::factory()->create(['ssn' => $ssn]);

        $response = $this->getJson('/api/v1/ucr-card-data/list?ssn=' . $ssn);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully by SSN')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.ssn', $ssn);
    }

    /**
     * Test search by student_id query parameter
     */
    public function test_list_endpoint_search_by_student_id()
    {
        Sanctum::actingAs($this->user);

        $studentId = '862123456';
        $record = UcrCardDataActual::factory()->create(['student_id' => $studentId]);

        $response = $this->getJson('/api/v1/ucr-card-data/list?student_id=' . $studentId);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully by student_id')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.student_id', $studentId);
    }

    /**
     * Test search by ISO query parameter
     */
    public function test_list_endpoint_search_by_iso()
    {
        Sanctum::actingAs($this->user);

        $iso = '12345';
        $record = UcrCardDataActual::factory()->create(['iso' => $iso]);

        $response = $this->getJson('/api/v1/ucr-card-data/list?iso=' . $iso);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully by ISO')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.iso', $iso);
    }

    /**
     * Test search by date range query parameters
     */
    public function test_list_endpoint_search_by_date_range()
    {
        Sanctum::actingAs($this->user);

        $startDate = '2024-01-01';
        $endDate = '2024-12-31';

        // Create records within the date range
        UcrCardDataActual::factory()->count(3)->create(['issued' => '2024-06-15']);
        // Create records outside the date range
        UcrCardDataActual::factory()->create(['issued' => '2023-12-31']);

        $response = $this->getJson('/api/v1/ucr-card-data/list?start_date=' . $startDate . '&end_date=' . $endDate);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully by date range')
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test search by net_id endpoint
     */
    public function test_search_by_net_id_endpoint()
    {
        Sanctum::actingAs($this->user);

        $netId = 'test001';
        UcrCardDataActual::factory()->count(2)->create(['net_id' => $netId]);

        $response = $this->getJson('/api/v1/ucr-card-data/net-id/' . $netId);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully')
            ->assertJsonCount(2, 'data');
    }

    /**
     * Test search by SSN endpoint
     */
    public function test_search_by_ssn_endpoint()
    {
        Sanctum::actingAs($this->user);

        $ssn = '123456789';
        $record = UcrCardDataActual::factory()->create(['ssn' => $ssn]);

        $response = $this->getJson('/api/v1/ucr-card-data/ssn/' . $ssn);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.ssn', $ssn);
    }

    /**
     * Test search by student_id endpoint
     */
    public function test_search_by_student_id_endpoint()
    {
        Sanctum::actingAs($this->user);

        $studentId = '862123456';
        $record = UcrCardDataActual::factory()->create(['student_id' => $studentId]);

        $response = $this->getJson('/api/v1/ucr-card-data/student-id/' . $studentId);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.student_id', $studentId);
    }

    /**
     * Test search by ISO endpoint
     */
    public function test_search_by_iso_endpoint()
    {
        Sanctum::actingAs($this->user);

        $iso = '12345';
        $record = UcrCardDataActual::factory()->create(['iso' => $iso]);

        $response = $this->getJson('/api/v1/ucr-card-data/iso/' . $iso);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully')
            ->assertJsonPath('data.id', $record->id)
            ->assertJsonPath('data.iso', $iso);
    }

    /**
     * Test search by date range POST endpoint
     */
    public function test_search_by_date_range_post_endpoint()
    {
        Sanctum::actingAs($this->user);

        // Create records within the date range
        UcrCardDataActual::factory()->count(2)->create(['issued' => '2024-06-15']);

        $requestData = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31'
        ];

        $response = $this->postJson('/api/v1/ucr-card-data/date-range', $requestData);

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'UCR card data retrieved successfully for date range')
            ->assertJsonCount(2, 'data.records');
    }

    /**
     * Test pagination with per_page parameter
     */
    public function test_list_endpoint_pagination_with_per_page()
    {
        Sanctum::actingAs($this->user);

        // Create test data
        UcrCardDataActual::factory()->count(10)->create();

        $response = $this->getJson('/api/v1/ucr-card-data/list?per_page=5');

        $response->assertStatus(200)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('data.per_page', 5)
            ->assertJsonPath('data.total', 10)
            ->assertJsonCount(5, 'data.data');
    }

    /**
     * Test 404 responses for not found records
     */
    public function test_endpoints_return_404_for_not_found_records()
    {
        Sanctum::actingAs($this->user);

        // Test list endpoint with query parameters
        $response = $this->getJson('/api/v1/ucr-card-data/list?net_id=nonexistent');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'No records found for the provided net_id');

        $response = $this->getJson('/api/v1/ucr-card-data/list?ssn=nonexistent');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'No record found for the provided SSN');

        // Test specific endpoints
        $response = $this->getJson('/api/v1/ucr-card-data/net-id/nonexistent');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode());

        $response = $this->getJson('/api/v1/ucr-card-data/ssn/nonexistent');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode());

        $response = $this->getJson('/api/v1/ucr-card-data/student-id/nonexistent');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode());

        $response = $this->getJson('/api/v1/ucr-card-data/iso/nonexistent');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode());
    }

    /**
     * Test validation errors for date range endpoint
     */
    public function test_date_range_endpoint_validation_errors()
    {
        Sanctum::actingAs($this->user);

        // Test missing required fields
        $response = $this->postJson('/api/v1/ucr-card-data/date-range', ['start_date' => '2024-01-01']);
        $response->assertStatus(422)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'Validation failed');

        // Test invalid date format
        $response = $this->postJson('/api/v1/ucr-card-data/date-range', [
            'start_date' => 'invalid-date',
            'end_date' => '2024-12-31'
        ]);
        $response->assertStatus(422)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'Validation failed');

        // Test end_date before start_date
        $response = $this->postJson('/api/v1/ucr-card-data/date-range', [
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01'
        ]);
        $response->assertStatus(422)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'Validation failed');
    }

    /**
     * Test invalid date format in query parameters
     */
    public function test_list_endpoint_invalid_date_format_in_query_params()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/ucr-card-data/list?start_date=invalid-date&end_date=2024-12-31');

        $response->assertStatus(400)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'Invalid date format. Please use YYYY-MM-DD format');
    }

    /**
     * Test no records found for date range
     */
    public function test_date_range_endpoints_return_404_when_no_records_found()
    {
        Sanctum::actingAs($this->user);

        // Test list endpoint with query parameters
        $response = $this->getJson('/api/v1/ucr-card-data/list?start_date=2025-01-01&end_date=2025-12-31');
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode())
            ->assertJsonPath('message', 'No records found for the provided date range');

        // Test POST endpoint
        $response = $this->postJson('/api/v1/ucr-card-data/date-range', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31'
        ]);
        $response->assertStatus(404)
            ->assertJsonPath('status_code', $response->getStatusCode());
    }

    /**
     * Test all endpoints require authentication
     */
    public function test_all_endpoints_require_authentication()
    {
        $endpoints = [
            ['GET', '/api/v1/ucr-card-data/list'],
            ['GET', '/api/v1/ucr-card-data/net-id/test'],
            ['GET', '/api/v1/ucr-card-data/ssn/123456789'],
            ['GET', '/api/v1/ucr-card-data/student-id/862123456'],
            ['GET', '/api/v1/ucr-card-data/iso/12345'],
            ['POST', '/api/v1/ucr-card-data/date-range'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertStatus(401);
        }
    }

    /**
     * Test response structure consistency
     */
    public function test_response_structure_consistency()
    {
        Sanctum::actingAs($this->user);

        $record = UcrCardDataActual::factory()->create();

        $endpoints = [
            '/api/v1/ucr-card-data/list?net_id=' . $record->net_id,
            '/api/v1/ucr-card-data/net-id/' . $record->net_id,
            '/api/v1/ucr-card-data/ssn/' . $record->ssn,
            '/api/v1/ucr-card-data/student-id/' . $record->student_id,
            '/api/v1/ucr-card-data/iso/' . $record->iso,
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data'
                ])
                ->assertJsonPath('status_code', $response->getStatusCode());
        }
    }
}
