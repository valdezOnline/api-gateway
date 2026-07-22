<?php

namespace App\Services\IdmsData\DataTransferObjects;

use App\Services\IdmsData\DataTransferObjects\BaseDataTransferObject;

class IdmsPersonData extends BaseDataTransferObject
{
    // TODO: Add and correct properties for the IDMS person data fields
    public ?string $firstName;
    public ?string $lastName;
    public ?string $email;
    public ?string $phoneNumber;
    public ?string $address;

    public function __construct(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?string $phoneNumber = null,
        ?string $address = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
        $this->address = $address;
    }

    public function isValid(): bool
    {
        return !empty($this->firstName) && !empty($this->lastName);
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'address' => $this->address,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            self::getNonEmptyStringOrNull($data, 'first_name'),
            self::getNonEmptyStringOrNull($data, 'last_name'),
            self::getNonEmptyStringOrNull($data, 'email'),
            self::getNonEmptyStringOrNull($data, 'phone_number'),
            self::getNonEmptyStringOrNull($data, 'address')
        );
    }
}