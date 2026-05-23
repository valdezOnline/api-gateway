<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\UpsertServiceProviderAccessRequest;
use App\Http\Resources\v1\ApiServiceProviderResource;
use App\Models\ApiServiceProvider;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class UserServiceProviderAccessController extends Controller
{
    use ApiResponses;

    public function index(Request $request, User $user)
    {
        $authError = $this->ensureCanManage($request);

        if (!empty($authError)) {
            return $authError;
        }

        $user->load('apiServiceProviders');

        return ApiServiceProviderResource::collection($user->apiServiceProviders);
    }

    public function upsert(UpsertServiceProviderAccessRequest $request, User $user, ApiServiceProvider $apiServiceProvider)
    {
        $authError = $this->ensureCanManage($request);

        if (!empty($authError)) {
            return $authError;
        }

        $user->apiServiceProviders()->syncWithoutDetaching([
            $apiServiceProvider->id => [
                'enabled' => $request->enabledValue(),
                'assigned_by_user_id' => $request->user()->id,
            ],
        ]);

        $user->load('apiServiceProviders');

        $updated = $user->apiServiceProviders->firstWhere('id', $apiServiceProvider->id);

        return $this->success('User service provider access updated.', new ApiServiceProviderResource($updated));
    }

    public function destroy(Request $request, User $user, ApiServiceProvider $apiServiceProvider)
    {
        $authError = $this->ensureCanManage($request);

        if (!empty($authError)) {
            return $authError;
        }

        $user->apiServiceProviders()->detach($apiServiceProvider->id);

        return $this->ok('User service provider access removed.');
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
