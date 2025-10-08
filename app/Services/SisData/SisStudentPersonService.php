<?php

namespace App\Services\SisData;

use App\Services\SisData\DataTransferObjects\SisStudentPersonData;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SisStudentPersonService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly string $singleDataMinutes,
        private readonly string $multiDataMinutes,
    ) {
    }

    public function searchStudentPerson(string $stringId)
    {
        try {
            Log::info('searchStudentPerson called with stringId: ' . $stringId);

            if (!$stringId) {
                return $this->error('No valid search criteria found. Please provide search parameters.', 400);
            }

            // Build the URL with query parameters
            $url = "{$this->baseUrl}/api/sis-data-api/v1/ethos/persons/net-id-or-sid?id=" . $stringId;
            Log::info('Fetching student person data from URL: ' . $url);
            return Cache::remember("sisStudentPersonSearch_$stringId", now()->addMinutes($this->singleDataMinutes), function () use ($url) {

                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                // $resp = $this->getResponse($url);

                //Check if successful
                if ($resp->successful()) {
                    $data = $resp->json(null, true); // Convert to array instead of stdClass
                    Log::info('searchStudentPerson: Successful response received', ['data' => $data]);
                    return $this->ok('Success', SisStudentPersonData::fromArray($data));
                }

                Log::warning('searchStudentPerson: API request failed', ['response' => $resp->json()]);
                return $this->error($resp->json(), $resp->status());
            });

        } catch (\ErrorException $errorException) {
            Log::error('searchStudentPerson: Exception occurred', ['error' => $errorException->getMessage()]);
            return $this->error($errorException->getMessage(), 500);
        }
    }


}
