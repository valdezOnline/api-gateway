<?php

namespace App\Services\SisData\DataTransferObjects;

use App\Services\SisData\DataTransferObjects\BaseDataTransferObject;
use Log;

class SisStudentPersonData extends BaseDataTransferObject
{
    public function __construct(
        public readonly ?string $personGuid,
        public readonly ?string $studentId, //credentials.bannerId
        public readonly ?string $netId, // credentials.bannerUserName        
        public readonly ?string $personalEmail, // emails.address - where type.emailType == 'personal'
        public readonly ?string $campusEmail, // emails.address - where type.emailType == 'school'
        // public readonly string $firstName,// names.firstName
        // public readonly string $middleName, // names.middleName
        // public readonly string $lastName, // names.lastName
        public readonly ?string $preferredFullName,// names.fullName
        public readonly ?string $mobilePhoneNumber, // ucrPhone.ph- where type == 'mobile'
        public readonly ?string $emergencyPhoneNumber, // ucrPhone.ph- where type == 'emergency'
    ) {
    }

    /**
     * Check if the person data has the minimum required information
     */
    public function isValid(): bool
    {
        return !empty($this->personGuid);
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
     * Get personal email with fallback to empty string
     */
    public function getPersonalEmailOrEmpty(): string
    {
        return $this->personalEmail ?? '';
    }

    /**
     * Get campus email with fallback to empty string
     */
    public function getCampusEmailOrEmpty(): string
    {
        return $this->campusEmail ?? '';
    }

    /**
     * Get preferred full name with fallback to empty string
     */
    public function getPreferredFullNameOrEmpty(): string
    {
        return $this->preferredFullName ?? '';
    }

    /**
     * Get mobile phone number with fallback to empty string
     */
    public function getMobilePhoneNumberOrEmpty(): string
    {
        return $this->mobilePhoneNumber ?? '';
    }

    /**
     * Get emergency phone number with fallback to empty string
     */
    public function getEmergencyPhoneNumberOrEmpty(): string
    {
        return $this->emergencyPhoneNumber ?? '';
    }

    /**
     * Get the primary email (campus email preferred, fallback to personal)
     */
    public function getPrimaryEmail(): ?string
    {
        return $this->campusEmail ?? $this->personalEmail;
    }

    /**
     * Convert to array with null handling
     */
    public function toArray(): array
    {
        return [
            'personGuid' => $this->personGuid,
            'studentId' => $this->studentId,
            'netId' => $this->netId,
            'personalEmail' => $this->personalEmail,
            'campusEmail' => $this->campusEmail,
            'preferredFullName' => $this->preferredFullName,
            'mobilePhoneNumber' => $this->mobilePhoneNumber,
            'emergencyPhoneNumber' => $this->emergencyPhoneNumber,
        ];
    }
    public static function fromArray(array $data): static
    {
        Log::info('SisStudentPersonData:fromArray called with data: ' . json_encode($data));

        // Handle case where data is null or empty
        if (empty($data)) {
            Log::warning('SisStudentPersonData:fromArray called with empty data');
            return new self(
                personGuid: null,
                studentId: null,
                netId: null,
                personalEmail: null,
                campusEmail: null,
                preferredFullName: null,
                mobilePhoneNumber: null,
                emergencyPhoneNumber: null,
            );
        }

        // Initialize variables to ensure they're always defined
        $studentId = null;
        $netId = null;
        $personalEmail = null;
        $campusEmail = null;
        $mobilePhoneNumber = null;
        $emergencyPhoneNumber = null;
        $fullName = null;

        // Credentials - bannerId and bannerUserName        
        Log::debug('Processing credentials: ' . json_encode(data_get($data, 'credentials', [])));
        $credentials = self::getArrayFromData($data, 'credentials');
        foreach ($credentials as $credential) {
            $credType = data_get($credential, 'type');
            $value = self::getNonEmptyStringOrNull($credential, 'value');

            if ($credType === 'bannerId' && $value) {
                $studentId = $value;
            }
            if ($credType === 'bannerUserName' && $value) {
                $netId = $value;
            }
        }

        // Email addresses - personal and school/campus
        Log::debug('Processing email addresses: ' . json_encode(data_get($data, 'emails', [])));
        $emails = self::getArrayFromData($data, 'emails');
        foreach ($emails as $email) {
            $emailType = strtolower(data_get($email, 'type.emailType', ''));
            $address = self::getValidEmailOrNull(data_get($email, 'address'));

            Log::debug('Email type: ' . $emailType);

            if (in_array($emailType, ['personal']) && $address) {
                Log::debug('Found personal email: ' . $address);
                $personalEmail = $address;
            }

            if (in_array($emailType, ['school', 'campus']) && $address) {
                Log::debug('Found school email: ' . $address);
                $campusEmail = $address;
            }
        }

        // Phone numbers - mobile and emergency
        Log::debug('Processing phone numbers: ' . json_encode(data_get($data, 'ucrPhone', [])));
        $phones = self::getArrayFromData($data, 'ucrPhone');
        foreach ($phones as $phone) {
            $phoneType = data_get($phone, 'type');
            $phoneNumber = self::getCleanPhoneOrNull(data_get($phone, 'ph'));

            if ($phoneType === 'mobile' && $phoneNumber) {
                $mobilePhoneNumber = $phoneNumber;
            }
            if ($phoneType === 'emergency' && $phoneNumber) {
                $emergencyPhoneNumber = $phoneNumber;
            }
        }

        // Get preferred full name
        $names = self::getArrayFromData($data, 'names');
        foreach ($names as $name) {
            if (data_get($name, 'preference') === 'preferred') {
                $fullName = self::getNonEmptyStringOrNull($name, 'fullName');
                break;
            }
        }

        // Get person GUID - this is the most critical field
        $personGuid = self::getNonEmptyStringOrNull($data, 'id');

        return new self(
            personGuid: $personGuid,
            studentId: $studentId,
            netId: $netId,
            personalEmail: $personalEmail,
            campusEmail: $campusEmail,
            preferredFullName: $fullName,
            mobilePhoneNumber: $mobilePhoneNumber,
            emergencyPhoneNumber: $emergencyPhoneNumber,
        );
    }
}