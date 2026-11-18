<?php

namespace App\Services\ExLibrisAlma;

use App\Helpers\EncryptionHelper;
use App\Services\ExLibrisAlma\DataTransferObjects\PatronData;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExLibrisAlmaPatronDataService
{
    use ApiResponses;

    public function __construct(
        private readonly string $key,
        private readonly string $baseUrl,
        private readonly int $singleDataMinutes,
        private readonly int $multiDataMinutes,
    ) {
    }

    public function Patron(string $stringId)
    {
        try {
            $url = "{$this->baseUrl}/almaws/v1/users/{$stringId}";
            $resp = Http::accept('application/json')
                ->get($url, "apikey=$this->key");

            if ($resp->ok()) {
                $data = (array) json_decode($resp->body());
                if (count($data) < 1) {
                    return $this->error('No Active Patron Found.', 404);
                }

                return $this->ok('Success', PatronData::fromArray($data));
            }

            return $this->error($resp->json(), $resp->status());
        } catch (\ErrorException $errorException) {
            return $this->error($errorException->getMessage(), 500);
        }
    }

    public function GuestLogin(string $stringCreds)
    {
        try {
            $decryptedCreds = EncryptionHelper::decrypt($stringCreds);
            Log::debug('Guest login credentials decrypted.');

            if (! is_string($decryptedCreds) || ! Str::contains($decryptedCreds, 'x0x0x')) {
                return $this->error('Invalid encrypted credentials format.', 400);
            }

            // NOTE: the separator 'x0x0x' is what the library-apps used as a separator. Please do not change it.
            $primaryId = trim(Str::before($decryptedCreds, 'x0x0x'));
            $password = Str::after($decryptedCreds, 'x0x0x');
            Log::debug('Guest login primaryId parsed.', ['primaryId' => $primaryId]);

            if ($primaryId === '' || $password === '') {
                return $this->error('Invalid encrypted credentials format.', 400);
            }

            $url = "{$this->baseUrl}/almaws/v1/users/" . rawurlencode($primaryId);
            $queryString = http_build_query([
                'op' => 'auth',
                'password' => $password,
                'apikey' => $this->key,
            ]);

            $resp = Http::accept('application/json')
                ->post("{$url}?{$queryString}");

            return match ($resp->status()) {
                204 => $this->ok('Success', 'Guest Login Successful'),
                default => $this->error('Invalid Login Credentials', $resp->status()),
            };
        } catch (\Throwable $throwable) {
            return $this->error($throwable->getMessage(), 500);
        }
    }

    public function Search()
    {
        $qryParams = request()->getQueryString();
        try {
            $url = "{$this->baseUrl}/almaws/v1/users";
            $resp = Http::accept('application/json')
                ->get($url, "{$qryParams}&expand=full&apikey=$this->key");

            if ($resp->ok()) {
                $data = (array) $resp->json('user');

                return $this->ok('Success', PatronData::fromCollection($data));
            }

            return $this->error($resp->json(), $resp->status());
        } catch (\ErrorException $errorException) {
            return $this->error($errorException->getMessage(), 500);
        }
    }
}
