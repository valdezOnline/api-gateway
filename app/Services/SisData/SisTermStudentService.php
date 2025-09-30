<?php

namespace App\Services\SisData;

use App\Services\SisData\DataTransferObjects\SisTermStudentData;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SisTermStudentService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly string $singleDataMinutes,
        private readonly string $multiDataMinutes,
    ) {
    }

    protected function getResponse($url)
    {
        try {
            // return $this->ok($url);
            $resp = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => $this->key,
                ])->get($url);

            //Check if successful
            if ($resp->successful()) {
                // return $this->ok($url);
                $data = $resp->json();
                return $this->ok('Success', $resp->json());
            }

            return $this->error($resp->json(), 404);

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }

    /**
     * Summary of sisTermStudent
     * Example Before Encoded 
     * {"userName": "netId", "term": {"code": "202440"}}
     * {"studentId": "8####", "term": {"isCurrentTerm": "Y"}}
     * {"studentId": "8####", "term": {"isCurrentTerm": "Y"}, "enrolledThisTerm": "Y"}
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
            return $this->error('No valid search criteria found. Please provide either netId and termCode or studentId and isCurrentTerm.', 400);
        }

        // Check for netId and termCode combination
        if (isset($params['netId']) && !empty($params['netId'])) {
            $netId = $params['netId'];
            $stringCriteria = '{"userName": "' . $netId . '"}';

            // Check if there is a parameter for TermCode
            if (isset($params['termCode']) && !empty($params['termCode'])) {
                $termCode = $params['termCode'];
                $stringCriteria = '{"userName": "' . $netId . '", "term": {"code": "' . $termCode . '"}}';
            }
        }
        // Check for studentId and isCurrentTerm combination
        elseif (isset($params['studentId']) && !empty($params['studentId'])) {
            $studentId = $params['studentId'];
            $stringCriteria = '{"studentId": "' . $studentId . '"}';

            // Check if there is a parameter for isCurrentTerm
            if (isset($params['isCurrentTerm']) && !empty($params['isCurrentTerm'])) {
                $isCurrentTerm = $params['isCurrentTerm'];
                $stringCriteria = '{"studentId": "' . $studentId . '", "term": {"isCurrentTerm": "' . $isCurrentTerm . '"}}';

                // Check if there is a parameter for enrolledThisTerm
                if (isset($params['enrolledThisTerm']) && !empty($params['enrolledThisTerm'])) {
                    $enrolledThisTerm = $params['enrolledThisTerm'];
                    $stringCriteria = '{"studentId": "' . $studentId . '", "term": {"isCurrentTerm": "' . $isCurrentTerm . '"}, "enrolledThisTerm": "' . $enrolledThisTerm . '"}';
                }
            }
        } else {
            return $this->error('No valid search criteria found. Please provide either netId (with optional termCode) or studentId (with optional isCurrentTerm).', 400);
        }

        // Encode the criteria
        $urlEncodedCriteria = urlencode($stringCriteria);

        Log::info('Generated criteria: ' . $stringCriteria);
        Log::info('URL encoded criteria: ' . $urlEncodedCriteria);

        try {
            return Cache::remember("sisTermStudent_$stringCriteria", now()->addMinutes($this->singleDataMinutes), function () use ($urlEncodedCriteria) {
                // Setup end-point url
                $url = "{$this->baseUrl}/api/sis-data-api/v1/ethos/x-students?criteria=$urlEncodedCriteria";

                Log::info('Making request to URL: ' . $url);

                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    $data = $resp->json();
                    Log::info('API response successful: ' . json_encode($data));
                    return $this->ok('Success', SisTermStudentData::fromArray($data));
                }

                Log::error('API response failed: ' . $resp->status() . ' - ' . json_encode($resp->json()));
                return $this->error($resp->json(), $resp->status());
            });

        } catch (\Exception $exception) {
            Log::error('Exception in SearchCriteria: ' . $exception->getMessage());
            return $this->error($exception->getMessage(), 500);
        }
    }

}
