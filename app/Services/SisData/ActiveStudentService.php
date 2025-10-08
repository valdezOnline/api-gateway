<?php

namespace App\Services\SisData;

use App\Services\SisData\DataTransferObjects\ActiveStudentData;
use App\Services\SisData\DataTransferObjects\SisStudentPersonData;
use App\Services\SisData\DataTransferObjects\SisTermStudentData;
use App\Services\SisData\SisStudentPersonService;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Log;

class ActiveStudentService
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

    public function activeStudent(string $stringId)
    {
        Log::info("Fetching active student data for ID: $stringId");

        try {
            // return Cache::remember("activeStudent_$stringId", now()->addMinutes($this->singleDataMinutes), function () use ($stringId) {
            // Setup end-point url
            $url = "{$this->baseUrl}/api/sis-data-api/v1/ethos/active-student/$stringId";

            Log::info("activeStudent: Constructed URL: $url");
            $resp = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => $this->key,
                ])->get($url);

            //Check if successful
            if ($resp->successful()) {
                // return $this->ok($url);
                Log::info("activeStudent: Successful response received.", context: [$resp->json()]);
                $data = $resp->json(null, true); // Convert to array instead of stdClass

                // Get the SisStudentPersonData using the studentId and the SisStudentPersonService
                // Extract studentId from different possible keys
                $studentId = data_get($data, 'studentId') ?? data_get($data, 'principalId') ?? null;
                Log::info("activeStudent: Extracted studentId: " . ($studentId ?? 'null'));

                if ($studentId) {
                    // Fetch SisStudentPersonData using the SisStudentPersonService
                    Log::info("activeStudent: Fetching SisStudentPersonData for studentId: $studentId");
                    $sisStudentPersonService = new SisStudentPersonService($this->key, $this->baseUrl, $this->singleDataMinutes, $this->multiDataMinutes);
                    $personDataResponse = $sisStudentPersonService->searchStudentPerson($studentId);

                    // Check if person data was successfully retrieved
                    if ($personDataResponse && method_exists($personDataResponse, 'getData')) {
                        $personData = ($personDataResponse->getData())->data ?? null;
                        $data['personData'] = $personData;
                        Log::info("activeStudent: Successfully fetched SisStudentPersonData:", ['personData' => $personData]);
                    } else {
                        Log::warning("activeStudent: Failed to fetch SisStudentPersonData for studentId: $studentId");
                        $data['personData'] = null;
                    }

                    // Fetch TermStudentData using the TermStudentService
                    Log::info("activeStudent: Fetching TermStudentData for studentId: $studentId");
                    $termStudentData = new TermStudentService($this->key, $this->baseUrl, $this->singleDataMinutes, $this->multiDataMinutes);
                    $termDataResponse = $termStudentData->searchCriteria("isCurrentTerm=Y&enrolledThisTerm=Y&studentId=$studentId");

                    // Check if term data was successfully retrieved
                    if ($termDataResponse && method_exists($termDataResponse, 'getData')) {
                        $termData = ($termDataResponse->getData())->data ?? null;
                        $data['termData'] = $termData;
                        Log::info("activeStudent: Successfully fetched SisTermStudentData:", ['termData' => $termData]);
                    } else {
                        Log::warning("activeStudent: Failed to fetch SisTermStudentData for studentId: $studentId");
                        $data['termData'] = null;
                    }
                } else {
                    Log::warning("activeStudent: No studentId found, skipping person and term data fetching");
                    $data['personData'] = null;
                    $data['termData'] = null;
                }

                Log::info("activeStudent: Final data before creating ActiveStudentData:", ['data' => $data]);

                return $this->ok('Success', ActiveStudentData::fromArray($data));
                // return $this->ok('Success', $resp->json());
            }

            return $this->error($resp->json(), 404);

            // }); // End Cache::remember


        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }

    public function activeStudents(Request $request)
    {
        try {
            $limit = $request->limit;
            $offset = $request->offset;

            return Cache::remember("activeStudents_limit_{$limit}_offset_$offset", now()->addRealMinutes($this->multiDataMinutes), function () use ($limit, $offset) {
                // Setup end-point url
                $url = "{$this->baseUrl}/api/sis-data-api/v1/ethos/active-student/list?limit={$limit}&offset={$offset}";
                //return $this->getResponse($url);

                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    // return $this->ok($url);                    
                    $data = (array) $resp->json('data');
                    return $this->ok('Success', collect($data)->map(fn(array $arrayData) => ActiveStudentData::fromArray($arrayData)));
                }

                return $this->error($resp->json(), 404);

            });


        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }
    public function activeStudentCount()
    {
        return $this->getResponse("{$this->baseUrl}/api/sis-data-api/v1/ethos/active-student/count");

    }
}