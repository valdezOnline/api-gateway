<?php

namespace App\Services\SisData\DataTransferObjects;

use App\Services\SisData\DataTransferObjects\TermStudentData;
use App\Services\SisData\DataTransferObjects\SisStudentPersonData;
use App\Services\SisData\DataTransferObjects\BaseDataTransferObject;
use stdClass;

class ActiveStudentData extends BaseDataTransferObject
{
    public function __construct(
        public readonly ?string $personGuid,
        public readonly ?string $studentId, //$principalId,
        public readonly ?string $netId, //$principalName,
        public readonly ?string $affiliationType,
        public readonly ?string $campus,
        public readonly ?string $nameCode,
        public readonly ?string $firstName,
        public readonly ?string $middleName,
        public readonly ?string $lastName,
        public readonly bool $nameDefault,
        public readonly bool $nameActive,
        public readonly ?string $addressTypeCode,
        public readonly ?string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly ?string $addressLine3,
        public readonly ?string $city,
        public readonly ?string $stateOrProvince,
        public readonly ?string $postalCode,
        public readonly ?string $country,
        public readonly bool $addressDefault,
        public readonly bool $addressActive,

        public readonly ?string $phoneType,
        public readonly ?string $phoneNumber,
        public readonly bool $phoneDefault,
        public readonly bool $phoneActive,
        public readonly ?string $emailType,
        public readonly ?string $emailAddress,
        public readonly bool $emailDefault,
        public readonly bool $emailActive,

        public readonly ?stdClass $studentPersonData,
        public readonly array $termStudentData,
    ) {
    }

    /**
     * Check if the student data has the minimum required information
     */
    public function isValid(): bool
    {
        return !empty($this->personGuid) && !empty($this->studentId);
    }

    /**
     * Get person GUID with fallback to empty string
     */
    public function getPersonGuidOrEmpty(): string
    {
        return $this->personGuid ?? '';
    }

    /**
     * Get student ID with fallback to empty string
     */
    public function getStudentIdOrEmpty(): string
    {
        return $this->studentId ?? '';
    }

    /**
     * Get net ID with fallback to empty string
     */
    public function getNetIdOrEmpty(): string
    {
        return $this->netId ?? '';
    }

    /**
     * Get full name by combining name parts
     */
    public function getFullName(): string
    {
        $parts = array_filter([
            $this->firstName,
            $this->middleName,
            $this->lastName
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get primary email address
     */
    public function getEmailAddressOrEmpty(): string
    {
        return $this->emailAddress ?? '';
    }

    /**
     * Get phone number with fallback to empty string
     */
    public function getPhoneNumberOrEmpty(): string
    {
        return $this->phoneNumber ?? '';
    }

    /**
     * Get full address as a formatted string
     */
    public function getFormattedAddress(): string
    {
        $addressParts = array_filter([
            $this->addressLine1,
            $this->addressLine2,
            $this->addressLine3,
            $this->city,
            $this->stateOrProvince,
            $this->postalCode,
            $this->country
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Check if address information is available
     */
    public function hasAddress(): bool
    {
        return !empty($this->addressLine1) || !empty($this->city);
    }

    /**
     * Check if contact information is available
     */
    public function hasContactInfo(): bool
    {
        return !empty($this->phoneNumber) || !empty($this->emailAddress);
    }

    /**
     * Convert to array with proper null handling
     */
    public function toArray(): array
    {
        return [
            'personGuid' => $this->personGuid,
            'studentId' => $this->studentId,
            'netId' => $this->netId,
            'affiliationType' => $this->affiliationType,
            'campus' => $this->campus,
            'nameCode' => $this->nameCode,
            'firstName' => $this->firstName,
            'middleName' => $this->middleName,
            'lastName' => $this->lastName,
            'fullName' => $this->getFullName(),
            'nameDefault' => $this->nameDefault,
            'nameActive' => $this->nameActive,
            'addressTypeCode' => $this->addressTypeCode,
            'addressLine1' => $this->addressLine1,
            'addressLine2' => $this->addressLine2,
            'addressLine3' => $this->addressLine3,
            'city' => $this->city,
            'stateOrProvince' => $this->stateOrProvince,
            'postalCode' => $this->postalCode,
            'country' => $this->country,
            'formattedAddress' => $this->getFormattedAddress(),
            'addressDefault' => $this->addressDefault,
            'addressActive' => $this->addressActive,
            'phoneType' => $this->phoneType,
            'phoneNumber' => $this->phoneNumber,
            'phoneDefault' => $this->phoneDefault,
            'phoneActive' => $this->phoneActive,
            'emailType' => $this->emailType,
            'emailAddress' => $this->emailAddress,
            'emailDefault' => $this->emailDefault,
            'emailActive' => $this->emailActive,
            'studentPersonData' => $this->studentPersonData,
            'termStudentData' => $this->termStudentData,
            'isValid' => $this->isValid(),
            'hasAddress' => $this->hasAddress(),
            'hasContactInfo' => $this->hasContactInfo(),
        ];
    }
    public static function fromArray(array $data): static
    {
        // Handle case where data is null or empty
        if (empty($data)) {
            return new self(
                personGuid: null,
                studentId: null,
                netId: null,
                affiliationType: null,
                campus: null,
                nameCode: null,
                firstName: null,
                middleName: null,
                lastName: null,
                nameDefault: false,
                nameActive: false,
                addressTypeCode: null,
                addressLine1: null,
                addressLine2: null,
                addressLine3: null,
                city: null,
                stateOrProvince: null,
                postalCode: null,
                country: null,
                addressDefault: false,
                addressActive: false,
                phoneType: null,
                phoneNumber: null,
                phoneDefault: false,
                phoneActive: false,
                emailType: null,
                emailAddress: null,
                emailDefault: false,
                emailActive: false,
                studentPersonData: null,
                termStudentData: [],
            );
        }

        return new self(
            personGuid: self::getNonEmptyStringOrNull($data, 'personGuid'),
            studentId: self::getNonEmptyStringOrNull($data, 'principalId') ?? self::getNonEmptyStringOrNull($data, 'studentId'),
            netId: self::getNonEmptyStringOrNull($data, 'principalName') ?? self::getNonEmptyStringOrNull($data, 'netId'),
            firstName: self::getNonEmptyStringOrNull($data, 'firstName'),
            middleName: self::getNonEmptyStringOrNull($data, 'middleName'),
            lastName: self::getNonEmptyStringOrNull($data, 'lastName'),
            addressTypeCode: self::getNonEmptyStringOrNull($data, 'addressTypeCode'),
            addressLine1: self::getNonEmptyStringOrNull($data, 'addressLine1'),
            addressLine2: self::getNonEmptyStringOrNull($data, 'addressLine2'),
            addressLine3: self::getNonEmptyStringOrNull($data, 'addressLine3'),
            city: self::getNonEmptyStringOrNull($data, 'city'),
            stateOrProvince: self::getNonEmptyStringOrNull($data, 'stateOrProvince'),
            postalCode: self::getNonEmptyStringOrNull($data, 'postalCode'),
            country: self::getNonEmptyStringOrNull($data, 'country'),
            addressDefault: self::getBooleanFromData($data, 'addressDefault'),
            addressActive: self::getBooleanFromData($data, 'addressActive'),

            phoneType: self::getNonEmptyStringOrNull($data, 'phoneType'),
            phoneNumber: self::getCleanPhoneOrNull(self::getNonEmptyStringOrNull($data, 'phoneNumber')),
            phoneDefault: self::getBooleanFromData($data, 'phoneDefault'),
            phoneActive: self::getBooleanFromData($data, 'phoneActive'),
            emailType: self::getNonEmptyStringOrNull($data, 'emailType'),
            emailAddress: self::getValidEmailOrNull(self::getNonEmptyStringOrNull($data, 'emailAddress')),
            emailDefault: self::getBooleanFromData($data, 'emailDefault'),
            emailActive: self::getBooleanFromData($data, 'emailActive'),

            affiliationType: self::getNonEmptyStringOrNull($data, 'affiliationType'),
            campus: self::getNonEmptyStringOrNull($data, 'campus'),
            nameCode: self::getNonEmptyStringOrNull($data, 'nameCode'),

            nameDefault: self::getBooleanFromData($data, 'nameDefault'),
            nameActive: self::getBooleanFromData($data, 'nameActive'),

            studentPersonData: data_get($data, 'personData'),
            termStudentData: self::getArrayFromData($data, 'termData'),
        );
    }
}