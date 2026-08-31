<?php

namespace Tests\Unit;

use App\Services\UcrPerson\UcrPersonService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UcrPersonServiceTest extends TestCase
{
    public function test_it_accepts_numeric_cache_minutes_from_config(): void
    {
        Http::fake([
            'https://example.test/api/person-api/v1/person?netId=testuser' => Http::response([
                'response' => [
                    'results' => [
                        'netId' => 'testuser',
                        'firstName' => 'Test',
                        'lastName' => 'User',
                    ],
                ],
            ], 200),
        ]);

        $service = new UcrPersonService(
            'test-key',
            'https://example.test',
            '60',
            '1440'
        );

        $response = $service->ucrPerson('testuser');

        $this->assertSame(200, $response->getStatusCode());
    }
}
