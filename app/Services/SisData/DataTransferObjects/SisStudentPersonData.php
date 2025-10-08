<?php

namespace App\Services\SisData\DataTransferObjects;

use Log;

class SisStudentPersonData
{
    public function __construct(
        public readonly string $personGuid,
        public readonly string $studentId, //credentials.bannerId
        public readonly string $netId, // credentials.bannerUserName        
        public readonly string $personalEmail, // emails.address - where type.emailType == 'personal'
        public readonly string $campusEmail, // emails.address - where type.emailType == 'school'
        // public readonly string $firstName,// names.firstName
        // public readonly string $middleName, // names.middleName
        // public readonly string $lastName, // names.lastName
        public readonly string $preferredFullName,// names.fullName
        public readonly string $mobilePhoneNumber, // ucrPhone.ph- where type == 'mobile'
        public readonly string $emergencyPhoneNumber, // ucrPhone.ph- where type == 'emergency'
    ) {
    }
    public static function fromArray(array $data): self
    {
        Log::info('SisStudentPersonData:fromArray called with data: ' . json_encode($data));
        $i = 0;
        $x = 0;

        // Credentials - bannerId and bannerUserName        
        Log::debug('Processing credentials: ' . json_encode(data_get($data, 'credentials', [])));
        foreach (data_get($data, 'credentials', []) as $credential) {
            if (data_get($credential, 'type') === 'bannerId') {
                $studentId = data_get($credential, 'value');
                // $i++;
            }
            if (data_get($credential, 'type') === 'bannerUserName') {
                $netId = data_get($credential, 'value');
                // $x++;
            }
        }

        // Email addresses - personal and school/campus
        Log::debug('Processing email addresses: ' . json_encode(data_get($data, 'emails', [])));
        foreach (data_get($data, 'emails', []) as $email) {
            if (data_get($email, 'type.emailType') === 'personal') {
                $personalEmail = data_get($email, 'address');
            }
            if (data_get($email, 'type.emailType') === 'school') {
                $campusEmail = data_get($email, 'address');
            }
        }

        // Phone numbers - mobile and emergency
        Log::debug('Processing phone numbers: ' . json_encode(data_get($data, 'ucrPhones', [])));
        foreach (data_get($data, 'ucrPhone', []) as $phone) {
            if (data_get($phone, 'type') === 'mobile') {
                $mobilePhoneNumber = data_get($phone, 'ph');
            }
            if (data_get($phone, 'type') === 'emergency') {
                $emergencyPhoneNumber = data_get($phone, 'ph');
            }
        }

        // Get preferred full name
        foreach (data_get($data, 'names', []) as $name) {
            if (data_get($name, 'preference') === 'preferred') {
                $fullName = data_get($name, 'fullName');
            }
        }

        return new self(
            personGuid: data_get($data, 'id') ?? '',
            studentId: $studentId ?? '',
            netId: $netId ?? '',
            personalEmail: $personalEmail ?? '',
            campusEmail: $campusEmail ?? '',
            // firstName: data_get($data, 'firstName') ?? '',
            // middleName: data_get($data, 'middleName') ?? '',
            // lastName: data_get($data, 'lastName') ?? '',
            preferredFullName: $fullName ?? '',
            mobilePhoneNumber: $mobilePhoneNumber ?? '',
            emergencyPhoneNumber: $emergencyPhoneNumber ?? '',
        );
    }
}