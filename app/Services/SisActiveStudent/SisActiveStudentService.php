<?php

namespace App\Services\SisActiveStudent;

use App\Services\SisActiveStudent\DataTransferObjects\SisActiveStudentData;
use App\Traits\ApiResponses;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SisActiveStudentService
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
                return $this->ok('Success', SisActiveStudentData::fromArray($data));
                // return $this->ok('Success', $resp->json(), count($data));
            }

            return $this->error($resp->json(), 404);

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }

    public function sisActiveStudent(string $stringId)
    {
        try {

            return Cache::remember("sisActiveStudent_$stringId", now()->addMinutes($this->singleDataMinutes), function () use ($stringId) {
                // Setup end-point url
                $url = "{$this->baseUrl}/api/sis-data-api/v1/ethos/active-student/$stringId";

                // return $this->ok($url);
                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    // return $this->ok($url);
                    $data = $resp->json();
                    return $this->ok('Success', SisActiveStudentData::fromArray($data));
                    // return $this->ok('Success', $resp->json());
                }

                return $this->error($resp->json(), 404);

            });


        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }


    public function sisActiveStudents(Request $request)
    {
        try {
            $limit = $request->limit;
            $offset = $request->offset;

            return Cache::remember("sisActiveStudents_limit_{$limit}_offset_$offset", now()->addRealMinutes($this->multiDataMinutes), function () use ($limit, $offset) {
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
                    return $this->ok('Success', collect($data)->map(fn(array $arrayData) => SisActiveStudentData::fromArray($arrayData)));
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