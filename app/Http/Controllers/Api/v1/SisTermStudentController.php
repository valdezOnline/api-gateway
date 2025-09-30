<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\SisData\SisTermStudentService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SisTermStudentController extends Controller
{

    use ApiResponses;

    /**
     * Summary of termStudent
     * Note: search criteria should already been URL encoded.
     * Example Before Encoded 
     * {"userName": "netId", "term": {"code": "202440"}}
     * {"studentId": "8####", "term": {"isCurrentTerm": "Y"}}
     * {"studentId": "8####", "term": {"isCurrentTerm": "Y"}, "enrolledThisTerm": "Y"}
     * @param  = string; $stringCriteria
     */
    public function searchCriteria(SisTermStudentService $sisTermStudentService, Request $request)
    {
        Log::info('searchCriteriaController called with request: ' . json_encode($request->all()));

        // Get the raw query string for the service to process
        $queryString = $request->getQueryString();

        if (!$queryString) {
            return response()->json([
                'success' => false,
                'message' => 'No search criteria provided. Please provide either netId and termCode or studentId and isCurrentTerm.',
                'data' => null
            ], 400);
        }

        return $sisTermStudentService->SearchCriteria($queryString);
    }

}
