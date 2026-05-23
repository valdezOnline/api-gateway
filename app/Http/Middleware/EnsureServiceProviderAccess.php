<?php

namespace App\Http\Middleware;

use App\Models\ApiServiceProvider;
use App\Models\Application;
use App\Models\User;
use App\Traits\ApiResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceProviderAccess
{
    use ApiResponses;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $serviceKey): Response
    {
        $principal = $request->user();

        if (empty($principal) || !($principal instanceof User || $principal instanceof Application)) {
            return $this->error('Unauthenticated.', 401);
        }

        $serviceProvider = ApiServiceProvider::query()
            ->where('service_key', $serviceKey)
            ->first();

        if (empty($serviceProvider)) {
            if (config('api_service_access.default_allow_when_unassigned', true)) {
                return $next($request);
            }

            return $this->error('Service provider access is not available.', 403);
        }

        if (!$serviceProvider->enabled) {
            return $this->error('Service provider access is not available.', 403);
        }

        $assignment = $principal->apiServiceProviders()
            ->where('api_service_provider_id', $serviceProvider->id)
            ->first();

        if (!empty($assignment)) {
            if (!$assignment->pivot->enabled) {
                return $this->error('You do not have access to this API service provider.', 403);
            }

            return $next($request);
        }

        if (config('api_service_access.default_allow_when_unassigned', true)) {
            return $next($request);
        }

        return $this->error('You do not have access to this API service provider.', 403);
    }
}
