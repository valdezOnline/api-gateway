<?php

namespace Tests\Feature\Api\v1;

use App\Models\ApiServiceProvider;
use App\Models\Application;
use App\Models\UcrCardDataActual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceProviderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_allow_mode_allows_unassigned_user_access(): void
    {
        config(['api_service_access.default_allow_when_unassigned' => true]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        UcrCardDataActual::factory()->create();

        $response = $this->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(200);
    }

    public function test_strict_mode_denies_unassigned_user_access(): void
    {
        config(['api_service_access.default_allow_when_unassigned' => false]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        ApiServiceProvider::create([
            'service_key' => 'ucr_card_data',
            'display_name' => 'UCR Card Data',
            'enabled' => true,
        ]);

        $response = $this->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You do not have access to this API service provider.');
    }

    public function test_strict_mode_allows_assigned_user_access(): void
    {
        config(['api_service_access.default_allow_when_unassigned' => false]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $provider = ApiServiceProvider::create([
            'service_key' => 'ucr_card_data',
            'display_name' => 'UCR Card Data',
            'enabled' => true,
        ]);

        $user->apiServiceProviders()->attach($provider->id, ['enabled' => true]);
        UcrCardDataActual::factory()->create();

        $response = $this->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(200);
    }

    public function test_strict_mode_denies_assigned_user_when_assignment_disabled(): void
    {
        config(['api_service_access.default_allow_when_unassigned' => false]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $provider = ApiServiceProvider::create([
            'service_key' => 'ucr_card_data',
            'display_name' => 'UCR Card Data',
            'enabled' => true,
        ]);

        $user->apiServiceProviders()->attach($provider->id, ['enabled' => false]);

        $response = $this->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(403);
    }

    public function test_strict_mode_allows_assigned_application_token_access(): void
    {
        config(['api_service_access.default_allow_when_unassigned' => false]);

        $application = Application::factory()->create();
        $provider = ApiServiceProvider::create([
            'service_key' => 'ucr_card_data',
            'display_name' => 'UCR Card Data',
            'enabled' => true,
        ]);

        $application->apiServiceProviders()->attach($provider->id, ['enabled' => true]);
        UcrCardDataActual::factory()->create();

        $token = $application->createToken('test-app-token', ['*'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/ucr-card-data/list');

        $response->assertStatus(200);
    }

    public function test_assignment_endpoints_require_admin_user_with_api_access(): void
    {
        $nonAdmin = User::factory()->create(['hasApiAccess' => 0]);
        $targetUser = User::factory()->create();
        $provider = ApiServiceProvider::create([
            'service_key' => 'ucr_card_data',
            'display_name' => 'UCR Card Data',
            'enabled' => true,
        ]);

        Sanctum::actingAs($nonAdmin);

        $response = $this->putJson(
            '/api/v1/users/' . $targetUser->id . '/service-providers/' . $provider->id,
            ['enabled' => true]
        );

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not allowed to manage service provider assignments.');
    }

    public function test_admin_can_upsert_list_and_remove_user_service_provider_assignment(): void
    {
        $admin = User::factory()->create(['hasApiAccess' => 1]);
        $targetUser = User::factory()->create();
        $provider = ApiServiceProvider::create([
            'service_key' => 'ucr_card_data',
            'display_name' => 'UCR Card Data',
            'enabled' => true,
        ]);

        Sanctum::actingAs($admin);

        $upsertResponse = $this->putJson(
            '/api/v1/users/' . $targetUser->id . '/service-providers/' . $provider->id,
            ['enabled' => true]
        );

        $upsertResponse->assertStatus(200)
            ->assertJsonPath('message', 'User service provider access updated.');

        $this->assertDatabaseHas('user_api_service_provider', [
            'user_id' => $targetUser->id,
            'api_service_provider_id' => $provider->id,
            'enabled' => 1,
            'assigned_by_user_id' => $admin->id,
        ]);

        $listResponse = $this->getJson('/api/v1/users/' . $targetUser->id . '/service-providers');

        $listResponse->assertStatus(200)
            ->assertJsonPath('data.0.attributes.serviceKey', 'ucr_card_data')
            ->assertJsonPath('data.0.attributes.accessEnabled', true);

        $deleteResponse = $this->deleteJson(
            '/api/v1/users/' . $targetUser->id . '/service-providers/' . $provider->id
        );

        $deleteResponse->assertStatus(200)
            ->assertJsonPath('message', 'User service provider access removed.');

        $this->assertDatabaseMissing('user_api_service_provider', [
            'user_id' => $targetUser->id,
            'api_service_provider_id' => $provider->id,
        ]);
    }
}
