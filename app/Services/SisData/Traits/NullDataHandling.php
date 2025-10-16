<?php

namespace App\Services\SisData\Traits;

trait NullDataHandling
{
    /**
     * Get non-empty string or null from data array
     */
    protected static function getNonEmptyStringOrNull(array $data, string $key): ?string
    {
        $value = data_get($data, $key);

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * Get boolean value with proper null handling
     */
    protected static function getBooleanFromData(array $data, string $key, bool $default = false): bool
    {
        $value = data_get($data, $key);

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $lower = strtolower(trim($value));
            return in_array($lower, ['true', 'yes', 'y', '1']);
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return $default;
    }

    /**
     * Get array value with proper null handling
     */
    protected static function getArrayFromData(array $data, string $key): array
    {
        $value = data_get($data, $key);
        return is_array($value) ? $value : [];
    }

    /**
     * Get numeric value as string or null
     */
    protected static function getNumericStringOrNull(array $data, string $key): ?string
    {
        $value = data_get($data, $key);

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return is_numeric($trimmed) ? $trimmed : null;
        }

        return null;
    }

    /**
     * Get float value or null
     */
    protected static function getFloatOrNull(array $data, string $key): ?float
    {
        $value = data_get($data, $key);

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * Get integer value or null
     */
    protected static function getIntOrNull(array $data, string $key): ?int
    {
        $value = data_get($data, $key);

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Validate email format
     */
    protected static function getValidEmailOrNull(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        $email = trim($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Clean and validate phone number
     */
    protected static function getCleanPhoneOrNull(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-digit characters except + at start
        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        // Basic validation - should have at least 10 digits
        if (strlen(preg_replace('/[^\d]/', '', $cleaned)) >= 10) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Safely concatenate non-empty strings
     */
    protected static function safeStringConcat(array $parts, string $separator = ' '): string
    {
        $nonEmptyParts = array_filter($parts, function ($part) {
            return is_string($part) && trim($part) !== '';
        });

        return implode($separator, array_map('trim', $nonEmptyParts));
    }

    /**
     * Check if data is considered empty/invalid
     */
    protected static function isEmptyData($data): bool
    {
        if (is_null($data)) {
            return true;
        }

        if (is_string($data)) {
            return trim($data) === '';
        }

        if (is_array($data)) {
            return empty($data);
        }

        return false;
    }

    /**
     * Convert array to clean array removing null/empty values
     */
    protected function toCleanArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_READONLY);

        $result = [];
        foreach ($properties as $property) {
            $value = $property->getValue($this);

            // Skip null values but keep false/0/empty arrays
            if ($value !== null) {
                $result[$property->getName()] = $value;
            }
        }

        return $result;
    }
}