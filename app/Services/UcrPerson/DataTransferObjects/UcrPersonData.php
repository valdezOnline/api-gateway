<?php

namespace App\Services\UcrPerson\DataTransferObjects;

use Illuminate\Database\Eloquent\Collection;
use PhpParser\Node\NullableType;

class UcrPersonData
{
    public function __construct(
        public readonly string $netId,
        public readonly string $displayName,
        public readonly string $emailAddress,
        public readonly string|array $eduPersonPrimaryAffiliation,
        public readonly string|array $eduPersonAffiliation,
        public readonly string $studentId,
        public readonly string $employeeId,
        public readonly string $firstName,
        public readonly string $middleName,
        public readonly string $lastName,
        public readonly string $phoneNumber,
        public readonly string $ucrOrg,
        public readonly string $ou,
        public readonly string $title,
        public readonly string $homeDepartmentCode,
        public readonly string $homeDepartment,
        public readonly string $ucrEnrollmentStatus,
        public readonly string $ucrCollege,
        public readonly string $ucrClassStanding,
        public readonly string $ucrLastRegistered,
        public readonly string $separationDate,
        public readonly bool $isActive,

        /* Other atrributes that can be added */
        // public readonly string $preferredFirstName,
        // public readonly string $preferredMiddleName,
        // public readonly string $preferredLastName,
        // public readonly string $officePhoneNumber,
        // public readonly string $udcIdentifier,
        // public readonly string $titleCode,
        // public readonly string $privacyCode,
        // public readonly string $organizationalStatus,
        // public readonly string $facultyStatus,
        // public readonly string $ucrEntryExpirationDate,
    ) {
    }

    public static function fromCollection(array $data): Collection
    {
        $personDataCollection = new Collection();

        for ($i = 0; $i < count($data); $i++) {
            # code...
            $self = new self(
                netId: addslashes(data_get($data, "$i.netId") ?? ''),
                displayName: addslashes(data_get($data, "$i.displayName") ?? ''),
                emailAddress: addslashes(data_get($data, "$i.emailAddress") ?? ''),
                eduPersonPrimaryAffiliation: data_get($data, "$i.eduPersonPrimaryAffiliation") ?? '',
                eduPersonAffiliation: data_get($data, "$i.eduPersonAffiliation") ?? '',
                studentId: data_get($data, "$i.ucrUniversityId") ?? '',
                employeeId: data_get($data, "$i.employeeId") ?? '',
                firstName: addslashes(data_get($data, "$i.firstName") ?? ''),
                middleName: addslashes(data_get($data, "$i.middleName") ?? ''),
                lastName: addslashes(data_get($data, "$i.lastName") ?? ''),
                phoneNumber: addslashes(data_get($data, "$i.phoneNumber") ?? ''),
                ucrOrg: data_get($data, "$i.ucrOrg") ?? '',
                ou: data_get($data, "$i.ou") ?? '',
                title: data_get($data, "$i.title") ?? '',
                homeDepartmentCode: data_get($data, "$i.homeDepartmentCode") ?? '',
                homeDepartment: data_get($data, "$i.homeDepartment") ?? '',
                ucrEnrollmentStatus: data_get($data, "$i.ucrEnrollmentStatus") ?? '',
                ucrCollege: data_get($data, "$i.ucrCollege") ?? '',
                ucrClassStanding: data_get($data, "$i.ucrClassStanding") ?? '',
                ucrLastRegistered: data_get($data, "$i.ucrLastRegistered") ?? '',
                separationDate: data_get($data, "$i.separationDate") ?? '',
                isActive: data_get($data, "$i.isActive") ?? '',

                // preferredFirstName: $data['preferredFirstName'] ?? '',
                // preferredMiddleName: $data['preferredMiddleName'] ?? '',
                // preferredLastName: $data['preferredLastName'] ?? '',
                // officePhoneNumber: $data['officePhoneNumber'] ?? '',
                // ucrUniversityId: $data['ucrUniversityId'] ?? '', //StudentId
                // udcIdentifier: $data['udcIdentifier'] ?? '',
                // titleCode: $data['titleCode'] ?? '',
                // privacyCode: $data['privacyCode'] ?? '',
                // organizationalStatus: $data['organizationalStatus'] ?? '',
                // facultyStatus: $data['facultyStatus'] ?? '',
                // ucrEntryExpirationDate: data_get($data, '0.ucrEntryExpirationDate') ?? '',
            );
            $personDataCollection->push($self);
        }

        return $personDataCollection;
    }

    public static function fromArray(array $data): self
    {
        $i = count($data) - 1;

        return new self(
            netId: addslashes(data_get($data, "$i.netId") ?? ''),
            displayName: addslashes(data_get($data, "$i.displayName") ?? ''),
            emailAddress: addslashes(data_get($data, "$i.emailAddress") ?? ''),
            eduPersonPrimaryAffiliation: data_get($data, "$i.eduPersonPrimaryAffiliation") ?? '',
            eduPersonAffiliation: data_get($data, "$i.eduPersonAffiliation") ?? '',
            studentId: data_get($data, "$i.ucrUniversityId") ?? '',
            employeeId: data_get($data, "$i.employeeId") ?? '',
            firstName: addslashes(data_get($data, "$i.firstName") ?? ''),
            middleName: addslashes(data_get($data, "$i.middleName") ?? ''),
            lastName: addslashes(data_get($data, "$i.lastName") ?? ''),
            phoneNumber: addslashes(data_get($data, "$i.phoneNumber") ?? ''),
            ucrOrg: data_get($data, "$i.ucrOrg") ?? '',
            ou: data_get($data, "$i.ou") ?? '',
            title: data_get($data, "$i.title") ?? '',
            homeDepartmentCode: data_get($data, "$i.homeDepartmentCode") ?? '',
            homeDepartment: data_get($data, "$i.homeDepartment") ?? '',
            ucrEnrollmentStatus: data_get($data, "$i.ucrEnrollmentStatus") ?? '',
            ucrCollege: data_get($data, "$i.ucrCollege") ?? '',
            ucrClassStanding: data_get($data, "$i.ucrClassStanding") ?? '',
            ucrLastRegistered: data_get($data, "$i.ucrLastRegistered") ?? '',
            separationDate: data_get($data, "$i.separationDate") ?? '',
            isActive: data_get($data, "$i.isActive") ?? '',

            // preferredFirstName: $data['preferredFirstName'] ?? '',
            // preferredMiddleName: $data['preferredMiddleName'] ?? '',
            // preferredLastName: $data['preferredLastName'] ?? '',
            // officePhoneNumber: $data['officePhoneNumber'] ?? '',
            // ucrUniversityId: $data['ucrUniversityId'] ?? '', //StudentId
            // udcIdentifier: $data['udcIdentifier'] ?? '',
            // titleCode: $data['titleCode'] ?? '',
            // privacyCode: $data['privacyCode'] ?? '',
            // organizationalStatus: $data['organizationalStatus'] ?? '',
            // facultyStatus: $data['facultyStatus'] ?? '',
            // ucrEntryExpirationDate: data_get($data, '0.ucrEntryExpirationDate') ?? '',
        );
    }

}