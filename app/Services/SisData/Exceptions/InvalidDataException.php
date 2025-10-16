<?php

namespace App\Services\SisData\Exceptions;

use Exception;
use Throwable;

class InvalidDataException extends Exception
{
    protected array $validationErrors;
    protected array $sourceData;

    public function __construct(
        string $message = '',
        array $validationErrors = [],
        array $sourceData = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->validationErrors = $validationErrors;
        $this->sourceData = $sourceData;

        if (empty($message) && !empty($validationErrors)) {
            $message = 'Data validation failed: ' . implode(', ', $validationErrors);
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Get source data that caused the validation error
     */
    public function getSourceData(): array
    {
        return $this->sourceData;
    }

    /**
     * Check if this exception has specific validation error
     */
    public function hasValidationError(string $error): bool
    {
        return in_array($error, $this->validationErrors);
    }

    /**
     * Get detailed error information for logging
     */
    public function getDetailedInfo(): array
    {
        return [
            'message' => $this->getMessage(),
            'validation_errors' => $this->validationErrors,
            'source_data' => $this->sourceData,
            'trace' => $this->getTraceAsString(),
        ];
    }

    /**
     * Create exception for missing required fields
     */
    public static function missingRequiredFields(array $requiredFields, array $sourceData = []): self
    {
        $errors = array_map(fn($field) => "Missing required field: {$field}", $requiredFields);

        return new self(
            message: 'Required fields are missing',
            validationErrors: $errors,
            sourceData: $sourceData
        );
    }

    /**
     * Create exception for invalid data format
     */
    public static function invalidFormat(string $field, $value, string $expectedFormat, array $sourceData = []): self
    {
        $error = "Invalid format for field '{$field}': expected {$expectedFormat}, got " . gettype($value);

        return new self(
            message: 'Invalid data format',
            validationErrors: [$error],
            sourceData: $sourceData
        );
    }

    /**
     * Create exception for empty data
     */
    public static function emptyData(string $context = ''): self
    {
        $message = 'Data is empty or null' . ($context ? " in context: {$context}" : '');

        return new self(
            message: $message,
            validationErrors: ['Empty or null data provided'],
            sourceData: []
        );
    }
}