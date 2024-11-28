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
        // public readonly 
        public readonly string $identifierNetId,
        public readonly string $identifierBarcode,

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
        $addressArrId = 0;
        // dd($addressArr);
        if ($addressArr !== null) {
            for ($i = 0; $i < count($addressArr); $i++) {
                if (data_get($data, "contact_info.address.$i.preferred") == true) {
                    $addressArrId = $i;
                }
            }
        }

        // Preferred Email Only
        $emailArr = data_get($data, 'contact_info.email');
        $emailArrId = 0;
        if ($emailArr !== null) {
            for ($i = 0; $i < count($emailArr); $i++) {
                if (data_get($data, "contact_info.email.$i.preferred") == true) {
                    $emailArrId = $i;
                }
            }
        }

        // Preferred Phone Only
        $phoneArr = data_get($data, 'contact_info.phone');
        $phoneArrId = 0;
        if ($phoneArr !== null) {
            for ($i = 0; $i < count($phoneArr); $i++) {
                if (data_get($data, "contact_info.phone.$i.preferred") == true) {
                    $phoneArrId = $i;
                }
            }
        }

        // NetId and Barcode Only
        $identifierArr = data_get($data, 'user_identifier');
        $identifierArrBarcodeId = 0;
        $identifierArrNetId = 0;
        if ($identifierArr !== null) {
            for ($i = 0; $i < count($identifierArr); $i++) {
                if (data_get($data, "user_identifier.$i.id_type.value") == 'BARCODE') {
                    $identifierArrBarcodeId = $i;
                }
                if (data_get($data, "user_identifier.$i.id_type.desc") == 'NetID') {
                    $identifierArrNetId = $i;
                }
            }
        }

        // dd("Barcode ArrId = $identifierArrBarcodeId", "NetId ArrId = $identifierArrNetId", );

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
        );
    }

    public static function fromCollection(array $data)
    {
        $patronCollection = new Collection();
        // dd($data);
        for ($i = 0; $i < count($data); $i++) {
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

                addressLine1: data_get($data, "$i.contact_info.address.$i.line1") ?? '',
                addressLine2: data_get($data, "$i.contact_info.address.$i.line2") ?? '',
                addressCity: data_get($data, "$i.contact_info.address.$i.city") ?? '',
                addressStateProvince: data_get($data, "$i.contact_info.address.$i.state_province") ?? '',
                addressPostalCode: data_get($data, "$i.contact_info.address.$i.postal_code") ?? '',
                addressCountry: data_get($data, "$i.contact_info.address.$i.country.value") ?? '',

                email: data_get($data, "$i.contact_info.email.$i.email_address") ?? '',
                phone: data_get($data, "$i.contact_info.phone.$i.phone_number") ?? '',
                identifierNetId: data_get($data, "$i.user_identifier.$i.value") ?? '',
                identifierBarcode: data_get($data, "$i.user_identifier.$i.value") ?? '',
            );
            $patronCollection->push($self);
        }

        return $patronCollection;
    }
}

