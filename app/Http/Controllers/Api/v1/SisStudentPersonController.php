<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\SisData\SisStudentPersonService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SisStudentPersonController extends Controller
{

    use ApiResponses;

    public function searchStudentPerson(SisStudentPersonService $sisStudentPersonService, Request $request)
    {
        Log::info('SisStudentPersonController:searchStudentPerson called with request: ' . json_encode($request->all()));

        // Get the raw query string for the service to process
        // $queryString = $request->stringId;

        if (!$request->stringId) {
            return response()->json([
                'success' => false,
                'message' => 'No search criteria provided. Please provide either netId and termCode or studentId and isCurrentTerm.',
                'data' => null
            ], 400);
        }

        return $sisStudentPersonService->searchStudentPerson($request->stringId);
    }

}
