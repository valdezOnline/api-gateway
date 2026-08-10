<?php

namespace App\Services\SisData;

use App\Services\SisData\DataTransferObjects\EducationalInstitutionUnitsData;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EducationalInstitutionUnitsService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly string $singleDataMinutes,
        private readonly string $multiDataMinutes,
    ) {
    }

    public function searchByType(string $criteria)
    {
        try {
            $allowedCriteria = ['department', 'college'];

            if (!in_array($criteria, $allowedCriteria, true)) {
                return $this->error('Invalid criteria. Allowed values are department or college.', 400);
            }

            $cacheKey = "educationalInstitutionUnits_{$criteria}";

            return Cache::remember($cacheKey, now()->addRealMinutes($this->multiDataMinutes), function () use ($criteria) {
                $url = "{$this->baseUrl}/api/sis-data-api/v1/ethos/educational-institution-units/list?refresh=true&criteria={$criteria}&criteriaPlaceholder=type";

                Log::info('Fetching educational institution units from URL: ' . $url);

                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                if ($resp->successful()) {
                    $records = (array) $resp->json('data', []);
                    $totalCount = $resp->json('totalCount', null);
                    $totalReturned = $resp->json('totalReturned', null);
                    $offset = $resp->json('offset', null);

                    $dtoRecords = collect($records)->map(fn(array $arrayData) => EducationalInstitutionUnitsData::fromArray($arrayData));

                    return response()->json([
                        'message' => 'Success',
                        'status' => 200,
                        'totalCount' => is_numeric($totalCount) ? (int) $totalCount : $totalCount,
                        'totalReturned' => is_numeric($totalReturned) ? (int) $totalReturned : $totalReturned,
                        'offset' => is_numeric($offset) ? (int) $offset : $offset,
                        'data' => $dtoRecords,
                    ], 200);
                }

                return $this->error($resp->json(), $resp->status());
            });
        } catch (\ErrorException $errorException) {
            Log::error('searchByType failed', ['error' => $errorException->getMessage()]);
            return $this->error($errorException->getMessage(), 500);
        }
    }
}
