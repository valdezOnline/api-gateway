<?php

namespace App\Services\ExLibrisAlma;

use App\Services\ExLibrisAlma\DataTransferObjects\FeeData;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Str;


class ExLibrisAlmaFeeDataService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly string $singleDataMinutes,
        private readonly string $multiDataMinutes,
    ) {
    }
    public function getResponse($url)
    {
        // return $this->ok($url);
        try {
            $resp = Http::acceptJson()
                ->get($url, "apikey=$this->key");

            //Check if successful
            if ($resp->ok()) {

                // If were are to return a more flatten data...
                $data = (array) $resp->json('fee');
                // dd(count($data));
                // Check if its empty
                if (count($data) < 1) {
                    return $this->error('No Fees Found.', 404);
                }

                // return $this->ok('Success', FeeData::fromArray($data));
                return $this->ok('Success', collect($data)->map(fn(array $arrayData) => FeeData::fromArray($arrayData)));
                // return $this->ok('Success', $resp->json());

            }

            // Return Error
            return $this->error($resp->json(), $resp->status());

        } catch (\ErrorException $errorException) {
            //throw $th;
            Cache::flush();
            return $this->error($errorException->getMessage(), 500);
        }
    }

    public function Fees(string $stringId)
    {
        try {

            $url = "{$this->baseUrl}/almaws/v1/users/{$stringId}/fees";

            return $this->getResponse($url);

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }

}