<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\ApiServiceProviderResource;
use App\Models\ApiServiceProvider;

class ApiServiceProvidersController extends Controller
{
    /**
     * Display all available API service providers.
     */
    public function index()
    {
        return ApiServiceProviderResource::collection(
            ApiServiceProvider::query()->orderBy('display_name')->get()
        );
    }
}
