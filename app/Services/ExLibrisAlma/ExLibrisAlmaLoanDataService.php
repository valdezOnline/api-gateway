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
        private readonly int $singleDataMinutes,
        private readonly int $multiDataMinutes,
    ) {
    }

    public function getResponse($url)
    {
        try {
            $separator = str_contains($url, '?') ? '&' : '?';
            $resp = Http::acceptJson()
                ->get($url . $separator . "apikey={$this->key}");

            //Check if successful
            if ($resp->ok()) {
                // If we are to return a more flatten data...
                $recCount = $resp->json('total_record_count');
                // Log the raw data for debugging purposes
                $data = (array) $resp->json('item_loan');
                // Log the raw data for debugging purposes
                Log::info('Raw loan data', [
                    'data' => $data,
                ]);
                $data = (array) $resp->json('item_loan');
                // Check if its empty
                if (count($data) < 1) {
                    return $this->error('No Loans Found.', 204);
                }

                $mappedData = collect($data)->map(fn(array $arrayData) => LoanData::fromArray($arrayData));

                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                    'data' => $mappedData,
                    'total_record_count' => $recCount,
                ], 200);

            }

            // Log the response if not successful
            Log::error('Failed to fetch successful response', [
                'response' => $resp->body(),
            ]);
            // Return error response if the request was not successful
            return $this->error($resp->json(), $resp->status());

        } catch (ErrorException $errorException) {
            // Log the error
            Log::error('Exception while fetching response', [
                'error' => $errorException->getMessage(),
            ]);
            return $this->error($errorException->getMessage(), 500);
        }
    }
    /**
     * Get loans for a specific user
     *
     * @param string $userId
     * @param string $loanStatus
     * @param string $limit
     * @param string $offset
     * @return \Illuminate\Http\JsonResponse
     */
    public function Loans(string $userId, string $limit = '50', string $offset = '0', string $loanStatus = 'COMPLETE')
    {
        try {
            // Capture the loan status, defaulting to 'COMPLETE' if not provided
            Log::info('Loan status before defaulting', [
                'loan_status' => $loanStatus,
            ]);            
            // Check of loan status is provided, if not default to 'COMPLETE'
            $loanStatus = $loanStatus ?: 'COMPLETE';

            Log::info('Loan status after defaulting', [
                'loan_status' => $loanStatus,
            ]);

            $url = "{$this->baseUrl}/almaws/v1/users/{$userId}/loans?loan_status={$loanStatus}&limit={$limit}&offset={$offset}";
            Log::info('Fetching user loans', [
                'user_id' => $userId,                
                'limit' => $limit,
                'offset' => $offset,
                'loan_status' => $loanStatus,
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
            $separator = str_contains($url, '?') ? '&' : '?';

            $resp = Http::acceptJson()
                ->get($url . $separator . "apikey={$this->key}");

            if ($resp->ok()) {
                $data = $resp->json('item_loan');

                if (empty($data)) {
                    return $this->error('No Loan Found.', 404);
                }

                if (is_array($data) && array_is_list($data)) {
                    $mappedData = collect($data)->map(fn(array $arrayData) => LoanData::fromArray($arrayData));

                    return $this->success('Success', $mappedData->all(), 200);
                }

                return $this->success('Success', LoanData::fromArray((array) $data), 200);
            }

            return $this->error($resp->json(), $resp->status());
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