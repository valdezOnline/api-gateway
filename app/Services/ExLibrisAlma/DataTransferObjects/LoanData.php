<?php

namespace App\Services\ExLibrisAlma\DataTransferObjects;

class LoanData
{
    public function __construct(
        public readonly string $link,
        public readonly string $id,
        public readonly string $circDeskValue,
        public readonly string $circDeskDesc,
        public readonly string $circDeskLink,
        public readonly array $returnCircDesk,
        public readonly string $libraryValue,
        public readonly string $libraryDesc,
        public readonly string $userPrimaryId,
        public readonly string $itemBarcode,
        public readonly string $dueDate,
        public readonly string $loanStatus,
        public readonly string $loanDate,
        public readonly string $processStatus,
        public readonly string $mmsId,
        public readonly string $holdingId,
        public readonly string $itemId,
        public readonly string $title,
        public readonly string $author,
        public readonly string $publicationYear,
        public readonly string $locationCodeValue,
        public readonly string $locationCodeName,
        public readonly string $itemPolicyValue,
        public readonly string $itemPolicyDescription,
        public readonly string $callNumber,
        public readonly string $lastRenewDate,
        public readonly string $lastRenewStatusValue,
        public readonly string $lastRenewStatusDesc,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            link: data_get($data, "link") ?? '',
            id: data_get($data, "loan_id") ?? '',
            circDeskValue: data_get($data, "circ_desk.value") ?? '',
            circDeskDesc: data_get($data, "circ_desk.desc") ?? '',
            circDeskLink: data_get($data, "circ_desk.link") ?? '',
            returnCircDesk: data_get($data, "return_circ_desk") ?? '',
            libraryValue: data_get($data, "library.value") ?? '',
            libraryDesc: data_get($data, "library.desc") ?? '',
            userPrimaryId: data_get($data, "user_id") ?? '',
            itemBarcode: data_get($data, "item_barcode") ?? '',
            dueDate: data_get($data, "due_date") ?? '',
            loanStatus: data_get($data, "loan_status") ?? '',
            loanDate: data_get($data, "loan_date") ?? '',
            processStatus: data_get($data, "process_status") ?? '',
            mmsId: data_get($data, "mms_id") ?? '',
            holdingId: data_get($data, "holding_id") ?? '',
            itemId: data_get($data, "item_id") ?? '',
            title: data_get($data, "title") ?? '',
            author: data_get($data, "author") ?? '',
            publicationYear: data_get($data, "publication_year") ?? '',
            locationCodeValue: data_get($data, "location_code.value") ?? '',
            locationCodeName: data_get($data, "location_code.name") ?? '',
            itemPolicyValue: data_get($data, "item_policy.value") ?? '',
            itemPolicyDescription: data_get($data, "item_policy.description") ?? '',
            callNumber: data_get($data, "call_number") ?? '',
            lastRenewDate: data_get($data, "last_renew_date") ?? '',
            lastRenewStatusValue: data_get($data, "last_renew_status.value") ?? '',
            lastRenewStatusDesc: data_get($data, "last_renew_status.desc") ?? '',
        );
    }
}
