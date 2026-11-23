<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ApiEntryRequest;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiAuthController extends Controller
{
    use ApiResponses;

    public function entry(ApiEntryRequest $request)
    {
        // Validate entries
        $request->validated($request->all());
        // return dd($request->all());


        try {
            // Get the application data
            $application = Application::where('name', $request->get('application'))
                ->where('status', '=', 1)
                ->first();


            // Check if application exists
            if (empty($application)) {
                return $this->error('Invalid application.', 401);
            }

            // Create the session user
            Session::put('apiInvoker', $application->name);

            // Prepare the data
            $expirationMinutes = (int) config('sanctum.expiration');
            $expiresAt = $expirationMinutes > 0 ? now()->addMinutes($expirationMinutes) : null;

            $data = [
                'name' => $application->name,
                'token' => $application->createToken(
                    'Application access token for ' . $application->name,
                    $application->tokenAbilitiesForGateway(),
                    $expiresAt
                )->plainTextToken
            ];

            // dd($data);
            return $this->ok('Authenticated', $data);

        } catch (NotFoundHttpException $notFoundHttpException) {
            return $this->error("Application Not Found! $notFoundHttpException", 404);
        } catch (\Error $er) {
            return $this->error("Error Occurred $er", 500);
        }

    }
    public function exit(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        // currentAccessToken() is typed as HasAbilities; only PersonalAccessToken can be deleted.
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return $this->ok('Successful Exit.');
    }
}