<?php

namespace App\DTO\Brand;

use App\Models\Brand;

class ExportBrandDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $status,
    ) {}

    public static function fromModel(Brand $brand): self
    {
        return new self(
            name: $brand->name,
            status: $brand->status,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}
