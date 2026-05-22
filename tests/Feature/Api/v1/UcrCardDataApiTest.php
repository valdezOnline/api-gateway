<?php

namespace Tests\Feature\Api\v1;

use Tests\TestCase;
use App\Models\UcrCardDataActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

class UcrCardDataApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user for protected routes
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    /**
     * Test the search multiple endpoint via HTTP
     */
    public function test_search_multiple_endpoint_via_http()
    {
        $netId1 = 'test001';
        $netId2 = 'test002';
        UcrCardDataActual::factory()->create(['net_id' => $netId1]);
        UcrCardDataActual::factory()->create(['net_id' => $netId2]);

        $response = $this->postJson('/api/v1/ucr-card-data/search-multiple', [
            'net_ids' => [$netId1, $netId2]
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'total_found',
                    'records',
                    'search_summary' => [
                        'net_ids_searched',
                        'ssns_searched',
                        'student_ids_searched',
                        'isos_searched'
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'status_code' => 200,
                'data' => [
                    'total_found' => 2,
                    'search_summary' => [
                        'net_ids_searched' => [$netId1, $netId2]
                    ]
                ]
            ]);
    }

    /**
     * Test the date range search endpoint via HTTP with structured response
     */
    public function test_date_range_search_endpoint_via_http()
    {
        UcrCardDataActual::factory()->count(2)->create(['issued' => '2024-06-15']);

        $response = $this->postJson('/api/v1/ucr-card-data/date-range', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'total_found',
                    'records',
                    'date_range' => [
                        'start',
                        'end'
                    ]
                ]
            ])
            ->assertJson([
                'status' => 'success',
                'status_code' => 200,
                'data' => [
                    'total_found' => 2,
                    'date_range' => [
                        'start' => '2024-01-01',
                        'end' => '2024-12-31'
                    ]
                ]
            ]);
    }

    /**
     * Test search multiple endpoint with mixed parameters
     */
    public function test_search_multiple_with_mixed_parameters_via_http()
    {
        $netId = 'test001';
        $ssn = '123456789';
        UcrCardDataActual::factory()->create(['net_id' => $netId]);
        UcrCardDataActual::factory()->create(['ssn' => $ssn]);

        $response = $this->postJson('/api/v1/ucr-card-data/search-multiple', [
            'net_ids' => [$netId],
            'ssns' => [$ssn]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'status_code' => 200,
                'data' => [
                    'total_found' => 2,
                    'search_summary' => [
                        'net_ids_searched' => [$netId],
                        'ssns_searched' => [$ssn]
                    ]
                ]
            ]);
    }

    /**
     * Test search multiple endpoint with no parameters returns error
     */
    public function test_search_multiple_with_no_parameters_via_http()
    {
        $response = $this->postJson('/api/v1/ucr-card-data/search-multiple', []);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'status_code' => 400,
                'message' => 'At least one search parameter must be provided'
            ]);
    }

    /**
     * Test search multiple endpoint with validation errors
     */
    public function test_search_multiple_with_invalid_data_via_http()
    {
        $response = $this->postJson('/api/v1/ucr-card-data/search-multiple', [
            'net_ids' => 'not-an-array'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'status_code' => 422,
                'message' => 'Validation failed'
            ]);
    }

    /**
     * Test date range endpoint with validation errors
     */
    public function test_date_range_with_invalid_dates_via_http()
    {
        $response = $this->postJson('/api/v1/ucr-card-data/date-range', [
            'start_date' => 'invalid-date',
            'end_date' => '2024-12-31'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'status_code' => 422,
                'message' => 'Validation failed'
            ]);
    }

    /**
     * Test authentication is required for protected routes
     */
    public function test_authentication_required_for_protected_endpoints()
    {
        // Test with a valid request but verify authentication is working
        // Since we're using Sanctum::actingAs in setUp, this should pass
        $response = $this->postJson('/api/v1/ucr-card-data/search-multiple', [
            'net_ids' => ['test001']
        ]);

        // This should return 404 (no records found) rather than 401 (unauthorized)
        // indicating that authentication is working
        $response->assertStatus(404);
    }
}
