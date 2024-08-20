<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\LoginUserRequest;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ApplicationAuthController extends Controller
{
    use ApiResponses;

    /**
     * Login
     * 
     * Authenticates the user and returns the user's API token.
     * 
     * @unauthenticated
     * @group Authentication
     * @response 200 {
    "data": {
        "token": "{YOUR_AUTH_KEY}"
    },
    "message": "Authenticated",
    "status": 200
    }
    */
    public function login(LoginUserRequest $request) {
        $request->validated($request->all());

        // Get the user
        $user = User::firstWhere('netid', $request->netid);

        // Check if user exists
        if (empty($user)){
            return $this->error('Invalid credentials', 401);
        }

        // Check if password matches
        if(!Hash::check($request->password, $user->password)){
            return $this->error('Invalid credentials', 401);
        }            

        // Successfully Authenticated
        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken(
                    'User access token for ' . $user->netid,
                    ['*'],
                    // Abilities::getAbilities($user),
                    now()->addDays(1))->plainTextToken
            ]
            );
    }

    /**
     * Logout
     * 
     * Signs out the user and destroy's the API token.
     * 
     * @group Authentication
     * @response 200 {}
     */
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Logged out successfully.');
    }
}