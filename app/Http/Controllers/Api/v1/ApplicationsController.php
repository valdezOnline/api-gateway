<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\v1\StoreApplicationRequest;
use App\Http\Requests\Api\v1\UpdateApplicationRequest;
use App\Http\Resources\v1\ApplicationResource;
use App\Models\Application;
use App\Http\Filters\v1\ApplicationFilter;
use Crypt;
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
        return new ApplicationResource(Application::Create(
            [
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => $request->createdBy,
                'apikey' => Crypt::encrypt($request->apikey),
                'status' => $request->status,
                'remember_token' => $rememberToken,
            ]
        ));
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
        //PATCH

        $application->update($request->mappedAttributes());

        return new ApplicationResource($application);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Application $application)
    {
        //
    }
}