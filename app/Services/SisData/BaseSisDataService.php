<?php

namespace App\Services\SisData;

use App\Services\SisData\Exceptions\InvalidDataException;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Http;

abstract class BaseSisDataService
{
    use ApiResponses;

    public function __construct(
        protected readonly string $key,
        protected readonly string $baseUrl,
        protected readonly string $singleDataMinutes,
        protected readonly string $multiDataMinutes,
    ) {
    }

    /**
     * Make a safe HTTP request with proper error handling
     */
    protected function makeRequest(string $url, string $cacheKey = null, int $cacheMinutes = null): JsonResponse
    {
        try {
            Log::info("Making request to: {$url}");

            // Use cache if key and minutes are provided
            if ($cacheKey && $cacheMinutes) {
                return Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () use ($url) {
                    return $this->executeRequest($url);
                });
            }

            return $this->executeRequest($url);

        } catch (InvalidDataException $e) {
            Log::error('Data validation error in SIS service', $e->getDetailedInfo());
            return $this->error([
                'message' => $e->getMessage(),
                'validation_errors' => $e->getValidationErrors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Unexpected error in SIS service', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error([
                'message' => 'An unexpected error occurred while fetching data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute the actual HTTP request
     */
    private function executeRequest(string $url): JsonResponse
    {
        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => $this->key,
            ])
            ->timeout(30) // Add timeout for better error handling
            ->get($url);

        // Check if response is successful
        if (!$response->successful()) {
            $statusCode = $response->status();
            $errorBody = $response->json();

            Log::warning("SIS API request failed", [
                'url' => $url,
                'status' => $statusCode,
                'response' => $errorBody,
            ]);

            return $this->error($errorBody ?? ['message' => 'Request failed'], $statusCode);
        }

        $data = $response->json();

        // Validate response data
        if ($data === null) {
            throw InvalidDataException::emptyData('API response');
        }

        Log::info("Request successful", ['url' => $url, 'data_present' => !empty($data)]);

        return $this->ok('Success', $data);
    }

    /**
     * Safely process array data with DTO conversion
     */
    protected function processArrayData(array $data, string $dtoClass): array
    {
        $results = [];
        $errors = [];

        foreach ($data as $index => $item) {
            try {
                if (!is_array($item)) {
                    Log::warning("Skipping non-array item at index {$index}", ['item' => $item]);
                    continue;
                }

                $dto = $dtoClass::fromArray($item);

                // Check if the DTO is valid
                if (!$dto->isValid()) {
                    Log::warning("Invalid DTO created from data at index {$index}", [
                        'errors' => $dto->getValidationErrors(),
                        'summary' => $dto->getSummary(),
                    ]);
                    $errors[] = "Invalid data at index {$index}: " . implode(', ', $dto->getValidationErrors());
                } else {
                    $results[] = $dto;
                }

            } catch (\Exception $e) {
                Log::error("Error processing data at index {$index}", [
                    'error' => $e->getMessage(),
                    'data' => $item,
                ]);
                $errors[] = "Error processing data at index {$index}: " . $e->getMessage();
            }
        }

        // Log summary
        Log::info("Data processing complete", [
            'total_items' => count($data),
            'successful' => count($results),
            'errors' => count($errors),
        ]);

        if (!empty($errors)) {
            Log::warning("Data processing had errors", ['errors' => $errors]);
        }

        return $results;
    }

    /**
     * Safely process single item with DTO conversion
     */
    protected function processSingleData(array $data, string $dtoClass)
    {
        try {
            $dto = $dtoClass::fromArray($data);

            if (!$dto->isValid()) {
                Log::warning("Invalid DTO created from data", [
                    'errors' => $dto->getValidationErrors(),
                    'summary' => $dto->getSummary(),
                ]);

                throw InvalidDataException::missingRequiredFields(
                    $dto->getValidationErrors(),
                    $data
                );
            }

            return $dto;

        } catch (InvalidDataException $e) {
            throw $e; // Re-throw validation exceptions

        } catch (\Exception $e) {
            Log::error("Error processing single data item", [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new InvalidDataException(
                message: 'Failed to process data: ' . $e->getMessage(),
                validationErrors: ['Processing error: ' . $e->getMessage()],
                sourceData: $data
            );
        }
    }

    /**
     * Get cache key for a specific request
     */
    protected function getCacheKey(string $operation, array $params = []): string
    {
        $baseKey = strtolower(class_basename(static::class)) . "_{$operation}";

        if (!empty($params)) {
            $paramString = http_build_query($params);
            $baseKey .= '_' . md5($paramString);
        }

        return $baseKey;
    }

    /**
     * Validate required parameters
     */
    protected function validateRequiredParams(array $params, array $required): void
    {
        $missing = [];

        foreach ($required as $param) {
            if (!array_key_exists($param, $params) || empty($params[$param])) {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            throw InvalidDataException::missingRequiredFields($missing, $params);
        }
    }
}