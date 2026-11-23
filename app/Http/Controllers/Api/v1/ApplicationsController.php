<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\StoreApplicationRequest;
use App\Http\Requests\Api\v1\UpdateApplicationRequest;
use App\Http\Resources\v1\ApplicationResource;
use App\Models\Application;
use App\Support\ApiServiceCatalog;
use App\Http\Filters\v1\ApplicationFilter;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplicationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ApplicationFilter $filters)
    {
        //
        return ApplicationResource::collection(Application::filter($filters)->paginate());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreApplicationRequest $request)
    {
        $rememberToken = Str::random(100);
        $selectedServices = $this->resolveSelectedServicesFromRequest($request);
        $allowedAbilities = $this->resolveAllowedAbilitiesFromRequest($request);
        if ($selectedServices !== null) {
            $allowedAbilities = $this->resolveAbilitiesFromSelectedServices($selectedServices);
        }

        if ($allowedAbilities === null) {
            $allowedAbilities = Application::GATEWAY_ABILITIES;
        }

        $allowedAbilities = Application::sanitizeGatewayAbilities($allowedAbilities);
        $apiKeyExpiresAt = $this->resolveApiKeyExpiresAt($request);

        $application = Application::create([
            'name' => $this->resolveInputValue($request, ['name', 'data.attributes.name']),
            'description' => $this->resolveInputValue($request, ['description', 'data.attributes.description']),
            'created_by' => $this->resolveInputValue($request, ['createdBy', 'data.attributes.createdBy']),
            'apikey' => $this->resolveInputValue($request, ['apikey', 'data.attributes.apikey']),
            'status' => (bool) $this->resolveInputValue($request, ['status', 'data.attributes.status'], true),
            'allowed_abilities' => $allowedAbilities,
            'assigned_services' => $selectedServices ?? [],
            'remember_token' => $rememberToken,
            'api_key_rotated_at' => now(),
            'api_key_revoked_at' => null,
            'api_key_expires_at' => $apiKeyExpiresAt,
        ]);

        return new ApplicationResource($application);
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application)
    {
        //
        return new ApplicationResource($application);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateApplicationRequest $request, Application $application)
    {
        $attributes = $request->mappedAttributes();
        $shouldRevokeKey = (bool) $this->resolveInputValue($request, ['data.attributes.revokeApiKey', 'revokeApiKey'], false);
        $shouldRenewKey = (bool) $this->resolveInputValue($request, ['data.attributes.renewApiKey', 'renewApiKey'], false);
        $apiKeyExpiresAt = $this->resolveApiKeyExpiresAt($request);

        if (array_key_exists('apikey', $attributes)) {
            $application->rotateApiKey((string) $attributes['apikey'], $apiKeyExpiresAt);
            unset($attributes['apikey']);
            unset($attributes['api_key_expires_at']);
        }

        if (array_key_exists('allowed_abilities', $attributes)) {
            $attributes['allowed_abilities'] = Application::sanitizeGatewayAbilities((array) $attributes['allowed_abilities']);
        }

        $selectedServices = $this->resolveSelectedServicesFromRequest($request);
        if ($selectedServices !== null) {
            $attributes['assigned_services'] = $selectedServices;
            $attributes['allowed_abilities'] = Application::sanitizeGatewayAbilities(
                $this->resolveAbilitiesFromSelectedServices($selectedServices)
            );
        }

        if ($shouldRenewKey) {
            $application->renewApiKey($apiKeyExpiresAt);
            unset($attributes['api_key_expires_at']);
        } elseif (array_key_exists('api_key_expires_at', $attributes)) {
            $attributes['api_key_expires_at'] = $apiKeyExpiresAt;
        }

        if ($shouldRevokeKey) {
            $application->revokeApiKey();
        } elseif (array_key_exists('status', $attributes) && (bool) $attributes['status'] === true) {
            $attributes['api_key_revoked_at'] = null;
        }

        if ($attributes !== []) {
            $application->update($attributes);
        }

        return new ApplicationResource($application);

    }

    public function rotateApiKey(Request $request, Application $application)
    {
        $validated = $request->validate([
            'apikey' => 'required|string|min:32|max:255',
            'apiKeyExpiresAt' => 'sometimes|date|after:today|before_or_equal:' . now()->addYear()->toDateString(),
        ]);

        $application->rotateApiKey(
            $validated['apikey'],
            isset($validated['apiKeyExpiresAt']) ? Carbon::parse($validated['apiKeyExpiresAt']) : now()->addYear()
        );

        return new ApplicationResource($application);
    }

    public function revokeApiKey(Application $application)
    {
        $application->revokeApiKey();

        return new ApplicationResource($application);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Application $application)
    {
        //
    }

    private function resolveAllowedAbilitiesFromRequest(Request $request): ?array
    {
        $candidate = $request->input('allowed_abilities');
        if (is_array($candidate)) {
            return $candidate;
        }

        $candidate = $request->input('allowedAbilities');
        if (is_array($candidate)) {
            return $candidate;
        }

        $candidate = $request->input('data.attributes.allowedAbilities');
        if (is_array($candidate)) {
            return $candidate;
        }

        $candidate = $request->input('data.attributes.allowed_abilities');
        if (is_array($candidate)) {
            return $candidate;
        }

        return null;
    }

    private function resolveSelectedServicesFromRequest(Request $request): ?array
    {
        $candidate = $this->resolveInputValue($request, [
            'selectedServices',
            'data.attributes.selectedServices',
            'assigned_services',
            'data.attributes.assigned_services',
        ]);

        if (!is_array($candidate)) {
            return null;
        }

        return array_values(array_unique(array_filter($candidate, fn ($item) => is_string($item) && $item !== '')));
    }

    private function resolveInputValue(Request $request, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if ($request->has($key)) {
                return $request->input($key);
            }
        }

        return $default;
    }

    private function resolveApiKeyExpiresAt(Request $request): Carbon
    {
        $value = $this->resolveInputValue($request, [
            'apiKeyExpiresAt',
            'api_key_expires_at',
            'data.attributes.apiKeyExpiresAt',
            'data.attributes.api_key_expires_at',
        ]);

        if (empty($value)) {
            return now()->addDays(Application::API_KEY_MAX_VALIDITY_DAYS);
        }

        return Carbon::parse((string) $value);
    }

    private function resolveAbilitiesFromSelectedServices(array $selectedServices): array
    {
        $catalogEntries = app(ApiServiceCatalog::class)->all();
        $catalogByKey = [];
        foreach ($catalogEntries as $entry) {
            $catalogByKey[$entry['key']] = $entry;
        }

        $unknownServices = array_values(array_filter($selectedServices, fn ($key) => !isset($catalogByKey[$key])));
        if ($unknownServices !== []) {
            throw ValidationException::withMessages([
                'selectedServices' => 'Unknown API services: ' . implode(', ', $unknownServices),
            ]);
        }

        $abilities = [];
        foreach ($selectedServices as $serviceKey) {
            foreach ($catalogByKey[$serviceKey]['requiredAbilities'] ?? [] as $ability) {
                $abilities[] = $ability;
            }
        }

        return array_values(array_unique($abilities));
    }
}