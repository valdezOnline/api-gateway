<?php

namespace App\Services\ExLibrisAlma;

use App\Helpers\EncryptionHelper;
use App\Services\ExLibrisAlma\DataTransferObjects\PatronData;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Request;
use Str;


class ExLibrisAlmaPatronDataService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly string $singleDataMinutes,
        private readonly string $multiDataMinutes,
    ) {
    }
    // public function getResponse($url)
    // {
    //     // return $this->ok($url);
    //     try {
    //         $resp = Http::acceptJson()
    //             ->get($url, "apikey=$this->key");

    //         //Check if successful
    //         if ($resp->ok()) {
    //             // dd($resp->body());
    //             // If were are to return a more flatten data...
    //             $data = (array) $resp->body();
    //             // dd(count($data));
    //             // Check if its empty
    //             if (count($data) < 1) {
    //                 return $this->error('No Active Patron Found.', 404);
    //             }

    //             // return $this->ok('Success', FeeData::fromArray($data));
    //             return $this->ok('Success', collect($data)->map(fn(array $arrayData) => PatronData::fromArray($arrayData)));
    //             // return $this->ok('Success', $resp->json());

    //         }

    //         // Return Error
    //         return $this->error($resp->json(), $resp->status());

    //     } catch (\ErrorException $errorException) {
    //         //throw $th;
    //         Cache::flush();
    //         return $this->error($errorException->getMessage(), 500);
    //     }
    // }

    public function Patron(string $stringId)
    {
        try {
            // dd($stringId);
            $url = "{$this->baseUrl}/almaws/v1/users/{$stringId}";
            $resp = Http::accept('application/json')
                ->get($url, "apikey=$this->key");

            // dd($resp->body());
            //Check if successful
            if ($resp->ok()) {
                // dd($resp->body());
                $data = (array) json_decode($resp->body());
                // dd($data);
                // Check if its empty
                if (count($data) < 1) {
                    return $this->error('No Active Patron Found.', 404);
                }

                // return $this->ok('Success', FeeData::fromArray($data));
                return $this->ok('Success', PatronData::fromArray($data));
                // return $this->ok('Success', $resp->json());

            }

            // Return Error
            return $this->error($resp->json(), $resp->status());

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }

    public function GuestLogin(string $stringCreds)
    {
        try {
            // Decrypt the creds
            // $decryptedCreds = decrypt($stringCreds);
            $decryptedCreds = EncryptionHelper::decrypt($stringCreds);

            // return $this->ok('Test-Success', $decryptedCreds);
            // dd($decryptedCreds);

            // Split the decrypted string
            // NOTE: the separator 'x0x0x' is what the library-apps used as a separator. Please do not change it.
            $primaryId = Str::before($decryptedCreds, 'x0x0x');
            $password = Str::after($decryptedCreds, 'x0x0x');
            // // dd($primaryId, $password);

            $url = "{$this->baseUrl}/almaws/v1/users/{$primaryId}?password={$password}&apikey=$this->key";
            $resp = Http::accept('application/json')
                ->post($url);

            // // dd($resp->status());
            // //Check if successful
            return match ($resp->status()) {
                204 => $this->ok('Success', 'Guest Login Successful'),
                default => $this->error('Invalid Login Credentials', $resp->status()),
            };

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }


    public function Search()
    {
        // dd(request()->getQueryString());
        $qryParams = request()->getQueryString();
        try {
            $url = "{$this->baseUrl}/almaws/v1/users";
            $resp = Http::accept('application/json')
                ->get($url, "{$qryParams}&expand=full&apikey=$this->key");


            //Check if successful
            if ($resp->ok()) {
                // dd($resp->body());
                // $data = (array) json_decode($resp->body());
                $data = (array) $resp->json('user');

                return $this->ok('Success', PatronData::fromCollection($data));
                // return $this->ok('Success', $data['user']);
                // return $this->ok('Success', $data);

            }

            // Return Error
            return $this->error($resp->json(), $resp->status());

        } catch (\ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }



}


