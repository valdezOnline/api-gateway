<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ApiEntryRequest;
use App\Http\Resources\v1\ApplicationResource;
use App\Models\Application;
use Hash;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiAuthController extends Controller
{
    use ApiResponses;
    public function entry(ApiEntryRequest $request)
    {
        // Validate entries
        $request->validated($request->all());

        try {
            // Get the application data
            $application = Application::firstWhere('name', $request->get('application'));

            // Prepare the data
            $data = [
                // $msg,
                // 'stored-encrypted-key' => $application->apikey,
                'name' => $application->name,
                'token' => $application->createToken(
                    'Application access token for ' . $application->name,
                    ['*'],
                    now()->addDays(1)
                )->plainTextToken
            ];

            return $this->ok('Authenticated', $data);

        } catch (NotFoundHttpException $notFoundHttpException) {
            return $this->error("Application Not Found! $notFoundHttpException", 404);
        } catch (\Error $er) {
            return $this->error("Error Occurred $er", 500);
        }

    }
    public function exit(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok('Successful Exit.');
    }
}