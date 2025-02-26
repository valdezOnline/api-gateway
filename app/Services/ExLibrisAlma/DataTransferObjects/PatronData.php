<?php

namespace App\Services\ExLibrisAlma\DataTransferObjects;

use Illuminate\Database\Eloquent\Collection;

class PatronData
{
    public function __construct(
        public readonly string $primaryId,
        public readonly string $firstName,
        public readonly string $middleName,
        public readonly string $lastName,
        public readonly string $recordType,
        public readonly string $fullName,
        public readonly string $userGroup,
        public readonly string $userGroupDesc,
        public readonly string $accountType,
        public readonly string $status,
        public readonly string $expiryDate,
        public readonly string $addressLine1,
        public readonly string $addressLine2,
        public readonly string $addressCity,
        public readonly string $addressStateProvince,
        public readonly string $addressPostalCode,
        public readonly string $addressCountry,
        public readonly string $email,
        public readonly string $phone,

        public readonly string $identifierNetId,
        public readonly string $identifierBarcode,
        public readonly string $identifierNetIdEmail,

        // public readonly string $createdBy,
        // public readonly string $createdDate,
        // public readonly string $lastModifiedBy,
        // public readonly string $lastModifiedDate,

    ) {
    }

    public static function fromArray(array $data): self
    {
        // dd(count($data));

        /// Contact Info Section
        // Preferred Address Only
        $addressArr = data_get($data, 'contact_info.address');
        $addressArrId = self::getPreferredContactInfoIndex($addressArr);

        // Preferred Email Only
        $emailArr = data_get($data, 'contact_info.email');
        $emailArrId = self::getPreferredContactInfoIndex($emailArr);

        // Preferred Phone Only
        $phoneArr = data_get($data, 'contact_info.phone');
        $phoneArrId = self::getPreferredContactInfoIndex($phoneArr);

        // NetId , NetIdEmail and Barcode Only
        $identifierArr = data_get($data, 'user_identifier');

        $identifierArrBarcodeId = self::getIdentifierIndex($identifierArr, 'Barcode');
        $identifierArrNetId = self::getIdentifierIndex($identifierArr, 'NetID');
        $identifierArrNetIdEmail = self::getIdentifierIndex($identifierArr, 'Additional ID 3');

        return new self(
            recordType: data_get($data, 'record_type.value') ?? '',
            primaryId: data_get($data, 'primary_id') ?? '',
            firstName: data_get($data, 'first_name') ?? '',
            middleName: data_get($data, 'middle_name') ?? '',
            lastName: data_get($data, 'last_name') ?? '',
            fullName: data_get($data, 'full_name') ?? '',
            userGroup: data_get($data, 'user_group.value') ?? '',
            userGroupDesc: data_get($data, 'user_group.desc') ?? '',
            accountType: data_get($data, 'account_type.value') ?? '',
            status: data_get($data, 'status.value') ?? '',
            expiryDate: data_get($data, 'expiry_date') ?? '',

            addressLine1: data_get($data, "contact_info.address.$addressArrId.line1") ?? '',
            addressLine2: data_get($data, "contact_info.address.$addressArrId.line2") ?? '',
            addressCity: data_get($data, "contact_info.address.$addressArrId.city") ?? '',
            addressStateProvince: data_get($data, "contact_info.address.$addressArrId.state_province") ?? '',
            addressPostalCode: data_get($data, "contact_info.address.$addressArrId.postal_code") ?? '',
            addressCountry: data_get($data, "contact_info.address.$addressArrId.country.value") ?? '',

            email: data_get($data, "contact_info.email.$emailArrId.email_address") ?? '',
            phone: data_get($data, "contact_info.phone.$phoneArrId.phone_number") ?? '',
            identifierNetId: data_get($data, "user_identifier.$identifierArrNetId.value") ?? '',
            identifierBarcode: data_get($data, "user_identifier.$identifierArrBarcodeId.value") ?? '',
            identifierNetIdEmail: data_get($data, "user_identifier.$identifierArrNetIdEmail.value") ?? '',
        );
    }

    public static function fromCollection(array $data)
    {

        $patronCollection = new Collection();
        // dd($data);
        for ($i = 0; $i < count($data); $i++) {
            /// Contact Info Section
            // Preferred Address Only            
            $addressArr = data_get($data[$i], "contact_info.address");
            $addressArrId = self::getPreferredContactInfoIndex($addressArr);

            // Preferred Email Only
            $emailArr = data_get($data[$i], "contact_info.email");
            $emailArrId = self::getPreferredContactInfoIndex($emailArr);

            // Preferred Phone Only
            $phoneArr = data_get($data[$i], "contact_info.phone");
            $phoneArrId = self::getPreferredContactInfoIndex($phoneArr);

            // NetId , NetIdEmail and Barcode Only
            $identifierArr = data_get($data[$i], "user_identifier");

            $identifierArrBarcodeId = self::getIdentifierIndex($identifierArr, 'Barcode');
            $identifierArrNetId = self::getIdentifierIndex($identifierArr, 'NetID');
            $identifierArrNetIdEmail = self::getIdentifierIndex($identifierArr, 'Additional ID 3');
            $self = new self(
                recordType: data_get($data, "$i.record_type.value") ?? '',
                primaryId: data_get($data, "$i.primary_id") ?? '',
                firstName: data_get($data, "$i.first_name") ?? '',
                middleName: data_get($data, "$i.middle_name") ?? '',
                lastName: data_get($data, "$i.last_name") ?? '',
                fullName: data_get($data, "$i.full_name") ?? '',
                userGroup: data_get($data, "$i.user_group.value") ?? '',
                userGroupDesc: data_get($data, "$i.user_group.desc") ?? '',
                accountType: data_get($data, "$i.account_type.value") ?? '',
                status: data_get($data, "$i.status.value") ?? '',
                expiryDate: data_get($data, "$i.expiry_date") ?? '',

                addressLine1: data_get($data, "$i.contact_info.address.$addressArrId.line1") ?? '',
                addressLine2: data_get($data, "$i.contact_info.address.$addressArrId.line2") ?? '',
                addressCity: data_get($data, "$i.contact_info.address.$addressArrId.city") ?? '',
                addressStateProvince: data_get($data, "$i.contact_info.address.$addressArrId.state_province") ?? '',
                addressPostalCode: data_get($data, "$i.contact_info.address.$addressArrId.postal_code") ?? '',
                addressCountry: data_get($data, "$i.contact_info.address.$addressArrId.country.value") ?? '',

                email: data_get($data, "$i.contact_info.email.$emailArrId.email_address") ?? '',
                phone: data_get($data, "$i.contact_info.phone.$phoneArrId.phone_number") ?? '',
                identifierNetId: data_get($data, "$i.user_identifier.$identifierArrNetId.value") ?? '',
                identifierBarcode: data_get($data, "$i.user_identifier.$identifierArrBarcodeId.value") ?? '',
                identifierNetIdEmail: data_get($data, "$i.user_identifier.$identifierArrNetIdEmail.value") ?? '',
            );
            $patronCollection->push($self);
        }

        return $patronCollection;
    }

    private static function getPreferredContactInfoIndex(array $contactTypeArr)
    {
        // Preferred Address Only
        if ($contactTypeArr !== null) {
            for ($i = 0; $i < count($contactTypeArr); $i++) {
                if (data_get($contactTypeArr, "$i.preferred") == true) {
                    return $i;
                }
            }
        }
    }

    private static function getIdentifierIndex(array $identifierArr, string $identifierDesc)
    {
        // Identifier Value
        if ($identifierArr !== null) {
            for ($i = 0; $i < count($identifierArr); $i++) {
                if (data_get($identifierArr, "$i.id_type.desc") == $identifierDesc) {
                    return $i;
                }
            }
        }

        // if ($identifierArr !== null) {
        //     for ($i = 0; $i < count($identifierArr); $i++) {
        //         if (data_get($data, "user_identifier.$i.id_type.value") == 'BARCODE') {
        //             $identifierArrBarcodeId = $i;
        //         }
        //         if (data_get($data, "user_identifier.$i.id_type.desc") == 'NetID') {
        //             $identifierArrNetId = $i;
        //         }
        //         if (data_get($data, "user_identifier.$i.id_type.desc") == 'Additional ID 3') {
        //             $identifierArrNetIdEmail = $i;
        //         }
        //     }
        // }
    }

}

