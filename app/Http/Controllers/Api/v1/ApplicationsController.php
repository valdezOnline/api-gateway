<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\StoreApplicationRequest;
use App\Http\Requests\Api\v1\UpdateApplicationRequest;
use App\Http\Resources\v1\ApplicationResource;
use App\Models\Application;
use App\Http\Filters\v1\ApplicationFilter;
use Illuminate\Support\Str;

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
        // dd($request->all());
        $rememberToken = Str::random(100);
        $allowedAbilities = Application::sanitizeGatewayAbilities(
            $this->resolveAllowedAbilitiesFromRequest($request) ?? Application::GATEWAY_ABILITIES
        );

        $application = Application::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $request->createdBy,
            'apikey' => $request->apikey,
            'status' => $request->status,
            'allowed_abilities' => $allowedAbilities,
            'remember_token' => $rememberToken,
            'api_key_rotated_at' => now(),
            'api_key_revoked_at' => null,
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
        $shouldRevokeKey = (bool) $request->input('data.attributes.revokeApiKey', false);

        if (array_key_exists('apikey', $attributes)) {
            $application->rotateApiKey((string) $attributes['apikey']);
            unset($attributes['apikey']);
        }

        if (array_key_exists('allowed_abilities', $attributes)) {
            $attributes['allowed_abilities'] = Application::sanitizeGatewayAbilities((array) $attributes['allowed_abilities']);
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
        ]);

        $application->rotateApiKey($validated['apikey']);

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
}