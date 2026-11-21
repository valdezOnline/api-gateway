<?php

namespace App\Services\IdmsCardData\DataTransferObjects;

use App\Services\IdmsCardData\DataTransferObjects\BaseDataTransferObject;

class IdmsCardData extends BaseDataTransferObject
{
    // TODO: Add and correct properties for the IDMS person data fields
    public ?string $netId;
    public ?string $ucrId;
    public ?string $studentId;
    public ?string $iso;
    public ?string $libraryCardNumber;
    public ?string $status1;
    public ?string $class;
    public ?string $yearInSchool;
    public ?string $studentFac;
    public ?string $proxInt;
    public ?string $issuedDate;
    public ?string $editDate; 
    public ?string $photoDate;
    public ?string $importedDate;
    

    public function __construct(
        ?string $netId = null,
        ?string $ucrId = null,
        ?string $studentId = null,
        ?string $iso = null,
        ?string $libraryCardNumber = null,
        ?string $status1 = null,
        ?string $class = null,
        ?string $yearInSchool = null,
        ?string $studentFac = null,
        ?string $proxInt = null,
        ?string $issuedDate = null,
        ?string $editDate = null,
        ?string $photoDate = null,
        ?string $importedDate = null
    ) {
        $this->netId = $netId;
        $this->ucrId = $ucrId;
        $this->studentId = $studentId;
        $this->iso = $iso;
        $this->libraryCardNumber = $libraryCardNumber;
        $this->status1 = $status1;
        $this->class = $class;
        $this->yearInSchool = $yearInSchool;
        $this->studentFac = $studentFac;
        $this->proxInt = $proxInt;
        $this->issuedDate = $issuedDate;
        $this->editDate = $editDate;
        $this->photoDate = $photoDate;
        $this->importedDate = $importedDate;
    }

    public function isValid(): bool
    {
        return !empty($this->netId) || !empty($this->ucrId) || !empty($this->studentId);
    }

    public function toArray(): array
    {
        return [
            'netId' => $this->netId,
            'ucrId' => $this->ucrId,
            'studentId' => $this->studentId,
            'iso' => $this->iso,
            'libraryCardNumber' => $this->libraryCardNumber,
            'status1' => $this->status1,
            'class' => $this->class,
            'yearInSchool' => $this->yearInSchool,
            'studentFac' => $this->studentFac,
            'proxInt' => $this->proxInt,
            'issuedDate' => $this->issuedDate,
            'editDate' => $this->editDate,
            'photoDate' => $this->photoDate,
            'importedDate' => $this->importedDate,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            self::stringOrNull($data, ['netId', 'net_id', 'userName', 'username']),
            self::stringOrNull($data, ['ucrId', 'ucr_id', 'ucrid', 'employeeId', 'employeId']),
            self::stringOrNull($data, ['studentId', 'student_id']),
            self::stringOrNull($data, ['iso', 'iso_number']),
            self::stringOrNull($data, ['libraryCardNumber', 'library_card_number', 'lib_num']),
            self::stringOrNull($data, ['status1', 'status_1']),
            self::stringOrNull($data, ['class']),
            self::stringOrNull($data, ['yearInSchool', 'year_in_school', 'yr_in_school']),
            self::stringOrNull($data, ['studentFac', 'student_fac', 'stud_fac']),
            self::stringOrNull($data, ['proxInt', 'prox_int']),
            self::stringOrNull($data, ['issuedDate', 'issued_date', 'issued']),
            self::stringOrNull($data, ['editDate', 'edit_date']),
            self::stringOrNull($data, ['photoDate', 'photo_date']),
            self::stringOrNull($data, ['importedDate', 'imported_date', 'imported'])
        );
    }

    private static function stringOrNull(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = self::getNonEmptyStringOrNull($data, $key);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }
}