<?php

namespace App\Services\ExLibrisAlma\DataTransferObjects;



class FeeData
{
    public function __construct(
        public readonly string $link,
        public readonly string $id,
        public readonly string $type,
        public readonly string $typeDesc,
        public readonly string $status,
        public readonly string $userPrimaryId,
        public readonly string $balance,
        public readonly string $remainingVatAmount,
        public readonly string $originalAmount,
        public readonly string $originalVatAmount,
        public readonly string $creationTime,
        public readonly string $statusTime,
        public readonly string $owner,
        public readonly string $ownerDesc,
        public readonly string $title,
        public readonly string $barcode,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            link: data_get($data, "link") ?? '',
            id: data_get($data, "id") ?? '',
            type: data_get($data, "type.value") ?? '',
            typeDesc: data_get($data, "type.desc") ?? '',
            status: data_get($data, "status.value") ?? '',
            userPrimaryId: data_get($data, "user_primary_id.value") ?? '',
            balance: data_get($data, "balance") ?? '',
            remainingVatAmount: data_get($data, "remaining_vat_amount") ?? '',
            originalAmount: data_get($data, "original_amount") ?? '',
            originalVatAmount: data_get($data, "original_vat_amount") ?? '',
            creationTime: data_get($data, "creation_time") ?? '',
            statusTime: data_get($data, "status_time") ?? '',
            owner: data_get($data, "owner.value") ?? '',
            ownerDesc: data_get($data, "owner.desc") ?? '',
            title: data_get($data, "title") ?? '',
            barcode: data_get($data, "barcode.value") ?? '',
        );
    }
}
