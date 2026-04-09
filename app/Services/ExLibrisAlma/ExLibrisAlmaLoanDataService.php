<?php

namespace App\Services\ExLibrisAlma;

use App\Services\ExLibrisAlma\DataTransferObjects\LoanData;
use App\Traits\ApiResponses;
use ErrorException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ExLibrisAlmaLoanDataService
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
        try {
            $resp = Http::acceptJson()
                ->get($url . "&apikey=$this->key");

            //Check if successful
            if ($resp->ok()) {
                // If were are to return a more flatten data...
                $recCount = $resp->json('total_record_count');
                // return $this->ok($recCount);
                $data = (array) $resp->json('item_loan');
                // Check if its empty
                if (count($data) < 1) {
                    return $this->error('No Loans Found.', 404);
                }

                $mappedData = collect($data)->map(fn(array $arrayData) => LoanData::fromArray($arrayData));

                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                    'data' => $mappedData,
                    'total_record_count' => $recCount,
                ], 200);

            }

            // Return Error
            return $this->error($resp->json(), $resp->status());

        } catch (ErrorException $errorException) {
            //throw $th;
            return $this->error($errorException->getMessage(), 500);
        }
    }
    /**
     * Get loans for a specific user
     *
     * @param string $userId
     * @param string $limit
     * @param string $offset
     * @return \Illuminate\Http\JsonResponse
     */
    public function Loans(string $userId, string $limit, string $offset)
    {
        try {

            $url = "{$this->baseUrl}/almaws/v1/users/{$userId}/loans?limit={$limit}&offset={$offset}";
            Log::info('Fetching user loans', [
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => $offset,
                'url' => $url,
            ]);
            // return $this->ok($url);
            return $this->getResponse($url);

        } catch (ErrorException $errorException) {
            Log::error('Exception while fetching user loans', [
                'user_id' => $userId,
                'error' => $errorException->getMessage(),
            ]);

            return $this->error($errorException->getMessage(), 500);
        }
    }

    /**
     * Get a specific loan
     *
     * @param string $userId
     * @param string $loanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function Loan(string $userId, string $loanId)
    {
        try {
            $url = "{$this->baseUrl}/almaws/v1/users/{$userId}/loans/{$loanId}";

            return $this->getResponse($url);
            // $response = Http::withHeaders([
            //     'Authorization' => 'apikey ' . $this->key,
            //     'Accept' => 'application/json',
            // ])->get("{$this->baseUrl}/almaws/v1/users/{$userId}/loans/{$loanId}");

            // if ($response->successful()) {
            //     return $response->json();
            // }

            return null;
        } catch (\Exception $errorException) {
            Log::error('Exception while fetching loan', [
                'user_id' => $userId,
                'loan_id' => $loanId,
                'error' => $errorException->getMessage(),
            ]);

            return $this->error($errorException->getMessage(), 500);
        }
    }

    /**
     * Renew a loan
     *
     * @param string $userId
     * @param string $loanId
     * @return \Illuminate\Http\JsonResponse
     */
    public function renewLoan(string $userId, string $loanId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'apikey ' . $this->key,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/almaws/v1/users/{$userId}/loans/{$loanId}", [
                        'op' => 'renew',
                    ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Exception while renewing loan', [
                'user_id' => $userId,
                'loan_id' => $loanId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}