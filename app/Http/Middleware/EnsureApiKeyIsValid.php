<?php

namespace App\Http\Middleware;

use App\Models\Application;
use App\Traits\ApiResponses;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKeyIsValid
{
    use ApiResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $applicationName = $request->input('application', $request->header('X-Application'));
        $providedApiKey = $request->input('apikey', $request->header('X-API-Key'));

        if (!is_string($applicationName) || !is_string($providedApiKey) || $applicationName === '' || $providedApiKey === '') {
            return $this->invalidKeyPair();
        }

        // Get the data
        $application = Application::firstWhere('name', $applicationName);

        // Check if there is data
        if (empty($application)) {
            return $this->invalidKeyPair();
        }
        if (!(bool) $application->status || $application->isApiKeyRevoked()) {
            return $this->invalidKeyPair();
        }

        $storedApiKey = (string) $application->apikey;

        if (Application::looksHashedApiKey($storedApiKey)) {
            if (!Hash::check($providedApiKey, $storedApiKey)) {
                return $this->invalidKeyPair();
            }
        } else {
            try {
                $decryptedStoredKey = Crypt::decrypt($storedApiKey);
            } catch (DecryptException) {
                return $this->invalidKeyPair();
            }

            if (!hash_equals($decryptedStoredKey, $providedApiKey)) {
                return $this->invalidKeyPair();
            }

            $application->rotateApiKey($providedApiKey);
        }

        $application->forceFill([
            'api_key_last_used_at' => now(),
            'api_key_last_used_ip' => $request->ip(),
        ])->save();

        if (!Hash::check($providedApiKey, (string) $application->apikey)) {
            return $this->invalidKeyPair();
        }

        return $next($request);
    }

    private function invalidKeyPair(): Response
    {
        return response()->json([
            'message' => 'Invalid application key pair.',
            'status' => 401,
        ], 401);
    }

}