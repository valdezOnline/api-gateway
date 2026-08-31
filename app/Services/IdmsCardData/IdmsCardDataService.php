<?php

namespace App\Services\IdmsCardData;


use App\Services\IdmsCardData\DataTransferObjects\IdmsCardData;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IdmsCardDataService
{
    use ApiResponses;
    //TODO: Add properties for the IDMS service configuration (e.g., API key, base URL, cache durations)    
    // Service methods and properties go here

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly int $singleDataMinutes,
        private readonly int $multiDataMinutes,
    ) {
    }

    /**
     * Summary of SearchCriteria
     * Note: search criteria should already been URL encoded.
     * Example Before Encoded 
     * {"netId": "staffNetId"}
     * @param  = string; $queryString
     */
    public function SearchCriteria(string $queryString)
    {

        Log::info('SearchCriteria called with query string: ' . $queryString);

        $stringCriteria = '';
        $urlEncodedCriteria = '';

        // Parse the query string
        parse_str($queryString, $params);

        if (empty($params)) {
            return $this->error('No valid search criteria found. Please provide either netId or studentId or UcrId (employeeId).', 400);
        }

        // Check if No Cache is requested
        if (isset($params['noCache']) && filter_var($params['noCache'], FILTER_VALIDATE_BOOLEAN)) {
            Log::info('No cache requested. Redirecting to no-cache method for this request.');
            return $this->searchCriteriaNoCache($queryString);
        }

        // Check for netId 
        if (isset($params['netId']) && !empty($params['netId'])) {
            $netId = $params['netId'];
            $stringCriteria = '{"netId": "' . $netId . '"}';
            // Check for studentId
        } elseif (isset($params['studentId']) && !empty($params['studentId'])) {
            $studentId = $params['studentId'];
            $stringCriteria = '{"studentId": "' . $studentId . '"}';
            // Check for employeId
        } elseif (isset($params['employeeId']) && !empty($params['employeeId'])) {
            $employeeId = $params['employeeId'] ?? $params['employeeId'];
            $stringCriteria = '{"ucrId": "' . $employeeId . '"}';
            // Check for ucrId
        } elseif (isset($params['ucrId']) && !empty($params['ucrId'])) {
            $ucrId = $params['ucrId'];
            $stringCriteria = '{"ucrId": "' . $ucrId . '"}';
        } else {
            return $this->error('No valid search criteria found. Please provide either netId or studentId or employeId (UcrId).', 400);
        }

        // Encode the criteria
        $urlEncodedCriteria = urlencode($stringCriteria);

        Log::info('Generated criteria: ' . $stringCriteria);
        Log::info('URL encoded criteria: ' . $urlEncodedCriteria);

        try {
            // Use caching to store the response for a specified duration
            return Cache::remember("idmsCardData_$stringCriteria", now()->addMinutes($this->singleDataMinutes), function () use ($urlEncodedCriteria) {
                // Setup end-point url
                $url = "{$this->baseUrl}/api/idms-api/v1/idms?criteria=$urlEncodedCriteria";

                Log::info('Making request to URL: ' . $url);

                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    $totalCount = $resp->json('totalCount', null);
                    $totalReturned = $resp->json('totalReturned', null);
                    $records = $this->extractRecords($resp->json());
                    Log::info('API response successful: ' . json_encode($records));
                    return $this->successWithTotalRecords($records, $totalCount, $totalReturned);
                }

                Log::error('API response failed: ' . $resp->status() . ' - ' . json_encode($resp->json()));
                return $this->error($resp->json(), $resp->status());
            });

        } catch (\Exception $exception) {
            Log::error('Exception in SearchCriteria: ' . $exception->getMessage());
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Summary of searchCriteriaNoCache
     * Note: search criteria should already been URL encoded.
     * Example Before Encoded 
     * {"netId": "staffNetId"}
     * @param string $queryString
     */
    public function searchCriteriaNoCache(string $queryString)
    {
        Log::info('searchCriteriaNoCache called with query string: ' . $queryString);

        $stringCriteria = '';
        $urlEncodedCriteria = '';

        // Parse the query string
        parse_str($queryString, $params);

        if (empty($params)) {
            return $this->error('No valid search criteria found. Please provide either netId or studentId or UcrId (employeId).', 400);
        }

        // Check for netId 
        if (isset($params['netId']) && !empty($params['netId'])) {
            $netId = $params['netId'];
            $stringCriteria = '{"netId": "' . $netId . '"}';
            // Check for studentId
        } elseif (isset($params['studentId']) && !empty($params['studentId'])) {
            $studentId = $params['studentId'];
            $stringCriteria = '{"studentId": "' . $studentId . '"}';
            // Check for employeId
        } elseif (isset($params['employeeId']) && !empty($params['employeeId'])) {
            $employeeId = $params['employeeId'] ?? $params['employeeId'];
            $stringCriteria = '{"ucrId": "' . $employeeId . '"}';
            // Check for ucrId
        } elseif (isset($params['ucrId']) && !empty($params['ucrId'])) {
            $ucrId = $params['ucrId'];
            $stringCriteria = '{"ucrId": "' . $ucrId . '"}';
        } else {
            return $this->error('No valid search criteria found. Please provide either netId or studentId or employeId (UcrId).', 400);
        }

        $urlEncodedCriteria = urlencode($stringCriteria);

        try {
            // Setup end-point url
            $url = "{$this->baseUrl}/api/idms-api/v1/idms?criteria=$urlEncodedCriteria";

            Log::info('Making request to URL: ' . $url);

            $resp = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => $this->key,
                ])->get($url);

            //Check if successful   
            if ($resp->successful()) {
                $totalCount = $resp->json('totalCount', null);
                $totalReturned = $resp->json('totalReturned', null);
                $records = $this->extractRecords($resp->json());
                Log::info('API response successful: ' . json_encode($records));
                return $this->successWithTotalRecords($records, $totalCount, $totalReturned);
            }

            Log::error('API response failed: ' . $resp->status() . ' - ' . json_encode($resp->json()));
            return $this->error($resp->json(), $resp->status());

        } catch (\Exception $exception) {
            Log::error('Exception in searchCriteriaNoCache: ' . $exception->getMessage());
            return $this->error($exception->getMessage(), 500);
        }

    }

    /**
     * Summary of AllIdmsCardData
     * Note: 
     * offset is the number of records to skip.
     * limit is the number of records to return. 
     * Per ITS min is 500 and max is 1000.
     * But the service will normalize the limit to be between 1 and 5000.
     * params does not need to be URL encoded.
     * Example Parameters (limit defaults to 1000 if not provided):
     * offset=0&limit=1000
     * offset=5000&limit=1000
     * 
     * @param int $offset
     * @param int $limit
     */
    public function AllIdmsCardData(int $offset, int $limit = 1000)
    {
        Log::info('AllIdmsCardData called with offset: ' . $offset . ' and limit: ' . $limit);

        try {
            $result = $this->fetchAllIdmsCardDataPage($offset, $limit, true);

            if ($result['success'] === true) {
                return $this->successWithTotalRecords(
                    $result['records'],
                    $result['totalCount'],
                    $result['totalReturned']
                );
            }

            return $this->error($result['error'], $result['status']);

        } catch (\Exception $exception) {
            Log::error('Exception in AllIdmsCardData: ' . $exception->getMessage());
            return $this->error($exception->getMessage(), 500);
        }
    }

    /**
     * Fetch and normalize one page of IDMS card data for internal sync jobs.
     */
    public function fetchAllIdmsCardDataPage(int $offset, int $limit = 1000, bool $useCache = false): array
    {
        Log::info('fetchAllIdmsCardDataPage called with offset: ' . $offset . ' and limit: ' . $limit . ' and useCache: ' . ($useCache ? 'true' : 'false'));

        if ($limit < 1 || $limit > 5000) {
            Log::error('Invalid limit value: ' . $limit . '. Limit must be between 1 and 5000.');

            return [
                'success' => false,
                'status' => 400,
                'totalCount' => null,
                'totalReturned' => null,
                'records' => [],
                'raw' => null,
                'error' => 'Invalid limit detected. Limit must be between 1 and 5000.',
            ];
        }

        $cacheKey = sprintf('idmsCardData_all_%d_%d', $offset, $limit);
        $fetcher = fn() => $this->requestAllIdmsCardDataPage($offset, $limit);

        if ($useCache) {
            return Cache::remember($cacheKey, now()->addMinutes($this->multiDataMinutes), $fetcher);
        }

        return $fetcher();
    }

    /**
     * Execute upstream request and return normalized payload metadata.
     */
    private function requestAllIdmsCardDataPage(int $offset, int $limit): array
    {
        try {
            $url = "{$this->baseUrl}/api/idms-api/v1/idms?offset=$offset&limit=$limit";

            Log::info('Making request to URL: ' . $url);

            $resp = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => $this->key,
                ])->get($url);

            if ($resp->successful()) {
                $raw = $resp->json();
                $totalCount = $resp->json('totalCount', null);
                $totalReturned = $resp->json('totalReturned', null);
                $records = $this->extractRecords($raw);

                return [
                    'success' => true,
                    'status' => 200,
                    'totalCount' => is_numeric($totalCount) ? (int) $totalCount : null,
                    'totalReturned' => is_numeric($totalReturned) ? (int) $totalReturned : count($records),
                    'records' => $records,
                    'raw' => $raw,
                    'error' => null,
                ];
            }

            $errorBody = $resp->json();
            Log::error('API response failed: ' . $resp->status() . ' - ' . json_encode($errorBody));

            return [
                'success' => false,
                'status' => $resp->status(),
                'totalCount' => null,
                'totalReturned' => null,
                'records' => [],
                'raw' => $errorBody,
                'error' => $errorBody,
            ];
        } catch (\Exception $exception) {
            Log::error('Exception in requestAllIdmsCardDataPage: ' . $exception->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'totalCount' => null,
                'totalReturned' => null,
                'records' => [],
                'raw' => null,
                'error' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Normalize upstream IDMS response payloads.
     * The service accepts either a raw array of rows or common nested envelopes.
     */
    private function extractRecords(mixed $response): array
    {
        if (is_array($response)) {
            if (array_is_list($response)) {
                return $response;
            }


            foreach (['data', 'response.results', 'results'] as $path) {
                $records = data_get($response, $path);

                if (is_array($records)) {
                    return array_is_list($records) ? $records : [$records];
                }
            }

            return [$response];
        }

        return [];
    }

    /**
     * Build a consistent success response with total record metadata.
     */
    private function successWithTotalRecords(array $records, ?int $totalCount, ?int $totalReturned)
    {
        $dtoRecords = collect($records)->map(fn(array $arrayData) => IdmsCardData::fromArray($arrayData));

        return response()->json([
            'message' => 'Success',
            'status' => 200,
            'totalCount' => $totalCount,
            'totalReturned' => $totalReturned,
            'data' => $dtoRecords,
        ], 200);
    }
}