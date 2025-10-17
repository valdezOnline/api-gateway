<?php

namespace App\Services\SisData\DataTransferObjects;

use App\Services\SisData\Traits\NullDataHandling;

abstract class BaseDataTransferObject
{
    use NullDataHandling;

    /**
     * Check if this DTO instance is valid based on required fields
     */
    abstract public function isValid(): bool;

    /**
     * Convert DTO to array representation
     */
    abstract public function toArray(): array;

    /**
     * Create DTO instance from array data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Get all validation errors for this DTO
     */
    public function getValidationErrors(): array
    {
        $errors = [];

        if (!$this->isValid()) {
            $errors[] = 'Required fields are missing or invalid';
        }

        return $errors;
    }

    /**
     * Check if DTO has any validation errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->getValidationErrors());
    }

    /**
     * Get a safe representation of this DTO with null values handled
     */
    public function toSafeArray(): array
    {
        return $this->toCleanArray();
    }

    /**
     * Convert DTO to JSON with proper null handling
     */
    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags | JSON_THROW_ON_ERROR);
    }

    /**
     * Create an empty/invalid instance of this DTO
     */
    public static function createEmpty(): static
    {
        return static::fromArray([]);
    }

    /**
     * Check if two DTOs are equivalent
     */
    public function equals(BaseDataTransferObject $other): bool
    {
        if (get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->toArray() === $other->toArray();
    }

    /**
     * Get a summary of this DTO for logging/debugging
     */
    public function getSummary(): array
    {
        return [
            'class' => get_class($this),
            'valid' => $this->isValid(),
            'errors' => $this->getValidationErrors(),
            'data_present' => !empty($this->toCleanArray()),
        ];
    }
}