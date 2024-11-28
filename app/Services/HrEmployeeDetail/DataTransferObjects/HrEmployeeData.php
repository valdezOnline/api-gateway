<?php

namespace App\Services\HrEmployeeDetail\DataTransferObjects;

use Illuminate\Database\Eloquent\Collection;

class HrEmployeeData
{
    public function __construct(
        public readonly string $employeeId,
        public readonly string $netId,
        public readonly string $firstName,
        public readonly string $middleName,
        public readonly string $lastName,
        public readonly string $phoneNumber,
        public readonly string $emailAddress,
        public readonly string $address1,
        public readonly string $address2,
        public readonly string $address3,
        public readonly string $address4,
        public readonly string $city,
        public readonly string $state,
        public readonly string $postCode,
        public readonly string $countryCode,
        public readonly string $employeeClassDesc,
        public readonly string $employeeStatus,
        public readonly string $jobCode,
        public readonly string $jobCodeDescription,
        public readonly string $supervisorNetId,
        public readonly string $supervisorFullName,
        public readonly string $departmentCode,
        public readonly string $departmentCodeDescription,


    ) {
    }

    public static function fromArray(array $data): self
    {
        $i = 0;
        $x = 0;

        // Names - PRF
        $namesArr = data_get($data, "$x.names");
        $nameId = '';
        for ($i = 0; $i < count($namesArr); $i++) {
            if (data_get($data, "$x.names.$i.type") === 'PRF') {
                $nameId = $i;
            }
        }
        // PhoneNumbers - HOME
        $phonesArr = data_get($data, "$x.phoneNumbers");
        $phoneId = '';
        for ($i = 0; $i < count($phonesArr); $i++) {
            if (data_get($data, "$x.phoneNumbers.$i.type") === 'HOME') {
                $phoneId = $i;
            }
        }

        // EmailAddress - Preferred
        $emailsArr = data_get($data, "$x.emailAddresses");
        $emailId = '';
        for ($i = 0; $i < count($emailsArr); $i++) {
            if (data_get($data, "$x.emailAddresses.$i.preferredEmail") === 'Y') {
                $emailId = $i;
            }
        }

        // Address - HOME
        $addressArr = data_get($data, "$x.addresses");
        $addressId = '';
        for ($i = 0; $i < count($addressArr); $i++) {
            if (data_get($data, "$x.addresses.$i.type") === 'HOME') {
                $addressId = $i;
            }
        }

        // Jobs - Primary
        $jobsArr = data_get($data, "$x.jobs");
        $jobsId = '';
        $supervisorNetId = '';
        $supervisorFullName = '';
        $employeeStatus = '';
        for ($i = 0; $i < count($jobsArr); $i++) {
            if (data_get($data, "$x.jobs.$i.isPrimary") === true) {
                $jobsId = $i;
                $supervisorNetId = data_get($data, "$x.jobs.$i.supervisor.netId") ?? '';
                $supervisorFullName = data_get($data, "$x.jobs.$i.supervisor.fullName") ?? '';
                $employeeStatus = data_get($data, "$x.jobs.$i.employeeStatus");
            }
        }

        // HrEmployeeData
        return new self(

            employeeId: data_get($data, "$x.employeeId") ?? '',
            netId: addslashes(data_get($data, "$x.netId") ?? ''),

            // Name
            firstName: addslashes(data_get($data, "$x.names.$nameId.firstName") ?? ''),
            middleName: addslashes(data_get($data, "$x.names.$nameId.middleName") ?? ''),
            lastName: addslashes(data_get($data, "$x.names.$nameId.lastName") ?? ''),
            // PhoneNumbers
            // phoneNumber: addslashes(data_get($data, "$x.phoneNumbers.$phoneId.phoneNumber") ?? ''),
            phoneNumber: str_replace('/', '-', data_get($data, "$x.phoneNumbers.$phoneId.phoneNumber") ?? ''),
            // EmailAddress
            emailAddress: data_get($data, "$x.emailAddresses.$emailId.emailAddress") ?? '',
            // Address
            address1: data_get($data, "$x.addresses.$addressId.address1") ?? '',
            address2: data_get($data, "$x.addresses.$addressId.address2") ?? '',
            address3: data_get($data, "$x.addresses.$addressId.address3") ?? '',
            address4: data_get($data, "$x.addresses.$addressId.address4") ?? '',
            city: data_get($data, "$x.addresses.$addressId.city") ?? '',
            state: data_get($data, "$x.addresses.$addressId.state") ?? '',
            postCode: data_get($data, "$x.addresses.$addressId.postCode") ?? '',
            countryCode: data_get($data, "$x.addresses.$addressId.countryCode") ?? '',
            // Jobs
            employeeClassDesc: data_get($data, "$x.jobs.$jobsId.employeeClassDesc") ?? '',
            employeeStatus:
            match ($employeeStatus) {
                'A' => 'Active',
                default => 'InActive',
            },
            jobCode: data_get($data, "$x.jobs.$jobsId.jobCode") ?? '',
            jobCodeDescription: data_get($data, "$x.jobs.$jobsId.jobCodeDescription") ?? '',
            supervisorNetId: $supervisorNetId ?? '',
            supervisorFullName: $supervisorFullName ?? '',
            departmentCode: data_get($data, "$x.jobs.$jobsId.department.code") ?? '',
            departmentCodeDescription: data_get($data, "$x.jobs.$jobsId.department.description") ?? '',

        );
    }

    public static function fromCollection(array $data): Collection
    {
        // Check the data
        //dd(count($data));
        $employeeCollection = new Collection();
        for ($x = 0; $x < count($data); $x++) {
            //$y = 0;
            //$x = 0;

            // Names - PRF
            $namesArr = data_get($data, "$x.names");
            // dd($namesArr);
            $nameId = '';
            for ($i = 0; $i < count($namesArr); $i++) {
                if (data_get($data, "$x.names.$i.type") === 'PRF') {
                    $nameId = $i;
                }
            }
            // dd($nameId);
            // PhoneNumbers - HOME
            $phonesArr = data_get($data, "$x.phoneNumbers");
            $phoneId = '';
            for ($i = 0; $i < count($phonesArr); $i++) {
                if (data_get($data, "$x.phoneNumbers.$i.type") === 'HOME') {
                    $phoneId = $i;
                }
            }

            // EmailAddress - Preferred
            $emailsArr = data_get($data, "$x.emailAddresses");
            $emailId = '';
            for ($i = 0; $i < count($emailsArr); $i++) {
                if (data_get($data, "$x.emailAddresses.$i.preferredEmail") === 'Y') {
                    $emailId = $i;
                }
            }

            // Address - HOME
            $addressArr = data_get($data, "$x.addresses");
            $addressId = '';
            for ($i = 0; $i < count($addressArr); $i++) {
                if (data_get($data, "$x.addresses.$i.type") === 'HOME') {
                    $addressId = $i;
                }
            }

            // Jobs - HOME
            $jobsArr = data_get($data, "$x.jobs");
            $jobsId = '';
            $supervisorNetId = '';
            $supervisorFullName = '';
            $employeeStatus = '';
            for ($i = 0; $i < count($jobsArr); $i++) {
                if (data_get($data, "$x.jobs.$i.isPrimary") === true) {
                    $jobsId = $i;
                    $supervisorNetId = data_get($data, "$x.jobs.$i.supervisor.netId") ?? '';
                    $supervisorFullName = data_get($data, "$x.jobs.$i.supervisor.fullName") ?? '';
                    $employeeStatus = data_get($data, "$x.jobs.$i.employeeStatus");
                }
            }

            $self = new self(

                employeeId: data_get($data, "$x.employeeId") ?? '',
                netId: addslashes(data_get($data, "$x.netId") ?? ''),
                // Name
                firstName: addslashes(data_get($data, "$x.names.$nameId.firstName") ?? ''),
                middleName: addslashes(data_get($data, "$x.names.$nameId.middleName") ?? ''),
                lastName: addslashes(data_get($data, "$x.names.$nameId.lastName") ?? ''),
                // PhoneNumbers
                // phoneNumber: addslashes(data_get($data, "$x.phoneNumbers.$phoneId.phoneNumber") ?? ''),
                phoneNumber: str_replace('/', '-', data_get($data, "$x.phoneNumbers.$phoneId.phoneNumber") ?? ''),
                // EmailAddress
                emailAddress: data_get($data, "$x.emailAddresses.$emailId.emailAddress") ?? '',
                // Address
                address1: data_get($data, "$x.addresses.$addressId.address1") ?? '',
                address2: data_get($data, "$x.addresses.$addressId.address2") ?? '',
                address3: data_get($data, "$x.addresses.$addressId.address3") ?? '',
                address4: data_get($data, "$x.addresses.$addressId.address4") ?? '',
                city: data_get($data, "$x.addresses.$addressId.city") ?? '',
                state: data_get($data, "$x.addresses.$addressId.state") ?? '',
                postCode: data_get($data, "$x.addresses.$addressId.postCode") ?? '',
                countryCode: data_get($data, "$x.addresses.$addressId.countryCode") ?? '',
                // Jobs
                employeeClassDesc: data_get($data, "$x.jobs.$jobsId.employeeClassDesc") ?? '',
                employeeStatus:
                match ($employeeStatus) {
                    'A' => 'Active',
                    default => 'InActive',
                },
                jobCode: data_get($data, "$x.jobs.$jobsId.jobCode") ?? '',
                jobCodeDescription: data_get($data, "$x.jobs.$jobsId.jobCodeDescription") ?? '',
                supervisorNetId: $supervisorNetId ?? '',
                supervisorFullName: $supervisorFullName ?? '',
                departmentCode: data_get($data, "$x.jobs.$jobsId.department.code") ?? '',
                departmentCodeDescription: data_get($data, "$x.jobs.$jobsId.department.description") ?? '',
            );
            $employeeCollection->push($self);
        }

        return $employeeCollection;
    }
}