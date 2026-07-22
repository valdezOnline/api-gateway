<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\IdmsCardData\IdmsCardDataService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IdmsCardDataController extends Controller
{
    use ApiResponses;

    private function resolveIdmsCardDataService(): IdmsCardDataService
    {
        try {
            return app(IdmsCardDataService::class);
        } catch (\Throwable $throwable) {
            return new IdmsCardDataService(
                config('services.idmsCardData.key'),
                config('services.idmsCardData.baseUrl'),
                config('services.idmsCardData.singleDataMinutes'),
                config('services.idmsCardData.multiDataMinutes')
            );
        }
    }

    /**
     * Summary of idmsCardData
     * Note: search criteria should already been URL encoded.
     * Example Before Encoded 
     * {"netId": "staffNetId"}
     * @param  = string; $stringCriteria
     */
    //
    public function searchCriteria(Request $request)
    {
        Log::info('searchCriteriaController called with request: ' . json_encode($request->all()));

        // Get the raw query string for the service to process
        $queryString = $request->getQueryString();

        if (!$queryString) {
            return response()->json([
                'success' => false,
                'message' => 'No search criteria provided. Please provide either netId or studentId or employeId (UcrId).',
                'data' => null
            ], 400);
        }

        return $this->resolveIdmsCardDataService()->SearchCriteria($queryString);
    }

    public function searchCriteriaNoCache(Request $request)
    {
        Log::info('searchCriteriaNoCacheController called with request: ' . json_encode($request->all()));

        // Get the raw query string for the service to process
        $queryString = $request->getQueryString();

        if (!$queryString) {
            return response()->json([
                'success' => false,
                'message' => 'No search criteria provided. Please provide either netId or studentId or employeId (UcrId).',
                'data' => null
            ], 400);
        }

        return $this->resolveIdmsCardDataService()->SearchCriteriaNoCache($queryString);
    }   

    public function getAllIdmsCardData(Request $request)
    {
        Log::info('getAllIdmsCardDataController called with request: ' . json_encode($request->all()));

        // Get the raw query string for the service to process
        $queryString = $request->getQueryString();

        if (!$queryString) {
            return response()->json([
                'success' => false,
                'message' => 'No search criteria provided. Please provide offset and limit parameters.',
                'data' => null
            ], 400);
        }

        // Parse the query string to extract offset and limit
        parse_str($queryString, $params);

        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
        $limit = isset($params['limit']) ? (int)$params['limit'] : 1000;

        return $this->resolveIdmsCardDataService()->GetAllIdmsCardData($offset, $limit);
    }
}
