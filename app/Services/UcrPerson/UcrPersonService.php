<?php

namespace App\Services\UcrPerson;

use App\Traits\ApiResponses;
use Http;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use App\Services\UcrPerson\DataTransferObjects\UcrPersonData;
use Str;

class UcrPersonService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly string $singleDataMinutes,
        private readonly string $multiDataMinutes,
    ) {
    }

    public function ucrPerson(string $stringId)
    {
        try {

            return Cache::remember("ucrPerson_$stringId", now()->addMinutes($this->singleDataMinutes), function () use ($stringId) {

                // Check if id is netId or empolyeeId
                $url = (Str::startsWith($stringId, '10')) ? "{$this->baseUrl}/api/person-api/v1/person?employeeId=$stringId" : "{$this->baseUrl}/api/person-api/v1/person?netId=$stringId";

                $resp = Http::acceptJson()
                    ->withHeaders([
                        'Authorization' => $this->key,
                    ])->get($url);

                //Check if successful
                if ($resp->successful()) {
                    // return $this->ok($url);
                    $data = (array) $resp->json('response.results');

                    // UcrPersonData
                    return $this->ok('Success', ucrPersonData::fromArray($data));
                }

                return $this->error($resp->json(), 404);

            });

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }

    public function ucrPersonSearch(string $searchField, string $searchTerm)
    {
        try {
            // return $this->ok("$searchField $searchTerm");
            return Cache::remember("ucrPerson_$searchField.$searchTerm", now()->addMinutes($this->singleDataMinutes), function () use ($searchField, $searchTerm) {

                // Create the URL
                $url = "{$this->baseUrl}/api/person-api/v1/person?searchField=$searchField&searchTerm=$searchTerm";
                // return $this->ok($url);

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

                    return $this->ok('Success', ucrPersonData::fromCollection($data));
                }

                return $this->error($resp->json('response'), 404);
            });


        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }
}