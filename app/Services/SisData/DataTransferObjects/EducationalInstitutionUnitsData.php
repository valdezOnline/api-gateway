<?php

namespace App\Services\SisData\DataTransferObjects;

class EducationalInstitutionUnitsData extends BaseDataTransferObject
{
    public function __construct(
        public readonly ?string $code,
        public readonly ?string $title,
        public readonly ?string $type,
    ) {
    }

    public function isValid(): bool
    {
        return !empty($this->code) || !empty($this->title) || !empty($this->type);
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'title' => $this->title,
            'type' => $this->type,
        ];
    }

    public static function fromArray(array $data): static
    {
        if (empty($data)) {
            return new self(
                code: null,
                title: null,
                type: null,
            );
        }

        return new self(
            code: self::getNonEmptyStringOrNull($data, 'code'),
            title: self::getNonEmptyStringOrNull($data, 'title'),
            type: self::getNonEmptyStringOrNull($data, 'type'),
        );
    }
}
