<?php

namespace App\Services\SisActiveStudent\DataTransferObjects;

class SisActiveStudentData
{
    public function __construct(
        public readonly string $personGuid,
        public readonly string $studentId, //$principalId,
        public readonly string $netId, //$principalName,
        public readonly string $affiliationType,
        public readonly string $campus,
        public readonly string $nameCode,
        public readonly string $firstName,
        public readonly string $middleName,
        public readonly string $lastName,
        public readonly bool $nameDefault,
        public readonly bool $nameActive,
        public readonly string $addressTypeCode,
        public readonly string $addressLine1,
        public readonly string $addressLine2,
        public readonly string $addressLine3,
        public readonly string $city,
        public readonly string $stateOrProvince,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly bool $addressDefault,
        public readonly bool $addressActive,

        public readonly string $phoneType,
        public readonly string $phoneNumber,
        public readonly bool $phoneDefault,
        public readonly bool $phoneActive,
        public readonly string $emailType,
        public readonly string $emailAddress,
        public readonly bool $emailDefault,
        public readonly bool $emailActive,

    ) {
    }
    public static function fromArray(array $data): self
    {
        $i = 0;
        return new self(
            personGuid: data_get($data, 'personGuid') ?? '',
            studentId: data_get($data, 'principalId') ?? '',
            netId: data_get($data, 'principalName') ?? '',
            firstName: data_get($data, 'firstName') ?? '',
            middleName: data_get($data, 'middleName') ?? '',
            lastName: data_get($data, 'lastName') ?? '',
            addressTypeCode: data_get($data, 'addressTypeCode') ?? '',
            addressLine1: data_get($data, 'addressLine1') ?? '',
            addressLine2: data_get($data, 'addressLine2') ?? '',
            addressLine3: data_get($data, 'addressLine3') ?? '',
            city: data_get($data, 'city') ?? '',
            stateOrProvince: data_get($data, 'stateOrProvince') ?? '',
            postalCode: data_get($data, 'postalCode') ?? '',
            country: data_get($data, 'country') ?? '',
            addressDefault: data_get($data, 'addressDefault') ?? '',
            addressActive: data_get($data, 'addressActive') ?? '',

            phoneType: data_get($data, 'phoneType') ?? '',
            phoneNumber: data_get($data, 'phoneNumber') ?? '',
            phoneDefault: data_get($data, 'phoneDefault') ?? '',
            phoneActive: data_get($data, 'phoneActive') ?? '',
            emailType: data_get($data, 'emailType') ?? '',
            emailAddress: data_get($data, 'emailAddress') ?? '',
            emailDefault: data_get($data, 'emailDefault') ?? '',
            emailActive: data_get($data, 'emailActive') ?? '',

            affiliationType: data_get($data, 'affiliationType') ?? '',
            campus: data_get($data, 'campus') ?? '',
            nameCode: data_get($data, 'nameCode') ?? '',

            nameDefault: data_get($data, 'nameDefault') ?? '',
            nameActive: data_get($data, 'nameActive') ?? '',

        );
    }
}