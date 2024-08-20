<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\ApiEntryRequest;
use App\Models\Application;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Crypt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiKeyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the data
        $application = Application::firstWhere('name', $request->get('application'));

        // Check if there is data
        if (empty($application)) {
            return $this->invalidKeyPair();
        }
        // Decrypt the stored key
        $decryptedStoredKey = Crypt::decrypt($application->apikey);

        // Check if the apikey matches
        if ($request->get('apikey') !== $decryptedStoredKey) {
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