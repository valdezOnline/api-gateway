<?php

namespace App\Services\HrEmployeeDetail;

use App\Services\HrEmployeeDetail\DataTransferObjects\HrEmployeeData;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Constraint\IsEmpty;
use Str;
use function PHPUnit\Framework\isEmpty;

class HrEmployeeDetailService
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
                // If were are to return a more flatten data...
                $data = (array) ($resp->json('response.results'));

                // Check if its empty
                if (count($data) < 1) {
                    return $this->error('Employee Not Found.', 404);
                }

                return $this->ok('Success', HrEmployeeData::fromArray($data));

            }

            return $this->error($resp->json(), 404);

        } catch (\ErrorException $errorException) {
            //throw $th;
            Cache::flush();
            return $this->error($errorException->getMessage(), 500);
        }
    }
    public function hrEmployeeDetail(string $netId)
    {
        try {

            return Cache::remember("hrEmployee_$netId", now()->addMinutes($this->singleDataMinutes), function () use ($netId) {
                // Setup end-point url
                $url = "{$this->baseUrl}/api/hr-api/v2/employeeDetails?netId=$netId";

                return $this->getResponse($url);

            });

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }
    public function hrEmployeeDetails(Request $request)
    {
        try {
            $qryStrings = $request->netIds;
            return Cache::remember("hrEmployee_details_$qryStrings", now()->addMinutes($this->multiDataMinutes), function () use ($request) {
                // Get ALL the netId parameters                
                $qryStrings = $request->netIds;
                $qryParams = '';
                foreach (explode('+', $qryStrings) as $value) {
                    # code...
                    $qryParams .= "&netId=$value";
                }
                $qryParams = Str::replaceFirst('&', '?', $qryParams);
                //dd($qryParams);

                // Setup end-point url
                $url = "{$this->baseUrl}/api/hr-api/v2/employeeDetails$qryParams";
                // return $this->getResponse($url);
                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    // Raw Data
                    // return $this->ok('Success', $resp->json('response.results'));

                    // UcrPersonData
                    $data = (array) $resp->json('response.results');
                    // return $this->ok('Success', count($data));

                    return $this->ok('Success', HrEmployeeData::fromCollection($data));
                }

                return $this->error($resp->json('response'), 404);

            });

        } catch (\ErrorException $errorException) {
            // throw $errorException;
            return $this->error($errorException->getMessage(), 500);
        }
    }
    public function hrEmployeeJob(Request $request)
    {
        try {
            //code...
            $netId = $request->netId;

            return Cache::remember("hrJob_$netId", now()->addMinutes($this->singleDataMinutes), function () use ($netId) {
                // Setup end-point url
                $url = "{$this->baseUrl}/api/hr-api/v2/job?netId=$netId";

                // return $this->getResponse($url);
                // return $this->ok($url);
                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    // return $this->ok($url);
                    // If were are to return a more flatten data...
                    $data = (array) ($resp->json('response.results'));

                    // Check if its empty
                    if (count($data) < 1) {
                        return $this->error('Employee Job Not Found.', 404);
                    }

                    return $this->ok('Success', $data);

                }

                return $this->error($resp->json(), 404);
            });

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }
}