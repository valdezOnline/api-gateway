<?php

namespace Tests\Feature\Api\v1;

use App\Helpers\EncryptionHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExLibrisAlmaGuestLoginApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        Sanctum::actingAs($user);
    }

    public function test_guest_login_accepts_string_creds_from_request_body(): void
    {
        Http::fake([
            '*' => Http::response('', 204),
        ]);

        $encryptedCreds = EncryptionHelper::encrypt('guest123x0x0xpass123');

        $response = $this->postJson('/api/v1/alma-user/patron/guestLogin', [
            'stringCreds' => $encryptedCreds,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Success',
                'status' => 200,
                'data' => 'Guest Login Successful',
            ]);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_contains($request->url(), '/almaws/v1/users/guest123')
                && str_contains($request->url(), 'op=auth')
                && str_contains($request->url(), 'password=pass123')
                && str_contains($request->url(), 'apikey=');
        });
    }

    public function test_guest_login_returns_422_when_string_creds_missing(): void
    {
        $response = $this->postJson('/api/v1/alma-user/patron/guestLogin', []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'stringCreds is required.',
                'status' => 422,
            ]);
    }

    public function test_guest_login_returns_400_for_invalid_encrypted_credentials(): void
    {
        $response = $this->postJson('/api/v1/alma-user/patron/guestLogin', [
            'stringCreds' => 'not-valid-encrypted-value',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid encrypted credentials format.',
                'status' => 400,
            ]);
    }
}
