<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UpsertServiceProviderAccessRequest;
use App\Http\Resources\v1\ApiServiceProviderResource;
use App\Models\ApiServiceProvider;
use App\Models\Application;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ApplicationServiceProviderAccessController extends Controller
{
    use ApiResponses;

    public function index(Request $request, Application $application)
    {
        $authError = $this->ensureCanManage($request);

        if (!empty($authError)) {
            return $authError;
        }

        $application->load('apiServiceProviders');

        return ApiServiceProviderResource::collection($application->apiServiceProviders);
    }

    public function upsert(UpsertServiceProviderAccessRequest $request, Application $application, ApiServiceProvider $apiServiceProvider)
    {
        $authError = $this->ensureCanManage($request);

        if (!empty($authError)) {
            return $authError;
        }

        $application->apiServiceProviders()->syncWithoutDetaching([
            $apiServiceProvider->id => [
                'enabled' => $request->enabledValue(),
                'assigned_by_user_id' => $request->user()->id,
            ],
        ]);

        $application->load('apiServiceProviders');

        $updated = $application->apiServiceProviders->firstWhere('id', $apiServiceProvider->id);

        return $this->success('Application service provider access updated.', new ApiServiceProviderResource($updated));
    }

    public function destroy(Request $request, Application $application, ApiServiceProvider $apiServiceProvider)
    {
        $authError = $this->ensureCanManage($request);

        if (!empty($authError)) {
            return $authError;
        }

        $application->apiServiceProviders()->detach($apiServiceProvider->id);

        return $this->ok('Application service provider access removed.');
    }

    private function ensureCanManage(Request $request)
    {
        $principal = $request->user();

        if (!($principal instanceof User)) {
            return $this->error('Only user principals can manage service provider assignments.', 403);
        }

        if (!$principal->hasApiAccess) {
            return $this->error('You are not allowed to manage service provider assignments.', 403);
        }

        return null;
    }
}
