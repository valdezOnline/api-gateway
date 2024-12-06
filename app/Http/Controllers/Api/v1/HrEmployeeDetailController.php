<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\HrEmployeeDetail\HrEmployeeDetailService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;


class HrEmployeeDetailController extends Controller
{
    //
    use ApiResponses;

    public function hrEmployee(HrEmployeeDetailService $hrEmployeeDetailService, Request $request)
    {
        return $hrEmployeeDetailService->HrEmployeeDetail($request->netId);
    }

    public function hrEmployeeDetails(HrEmployeeDetailService $hrEmployeeDetailService, Request $request)
    {
        return $hrEmployeeDetailService->HrEmployeeDetails($request);
    }

    public function hrEmployeeJob(HrEmployeeDetailService $hrEmployeeDetailService, Request $request)
    {
        return $hrEmployeeDetailService->hrEmployeeJob($request);
    }
}