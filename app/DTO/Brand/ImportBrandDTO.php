<?php

namespace App\DTO\Brand;

class ImportBrandDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $status = 'active'
    ) {}

    public static function fromExcelRow(array $row): self
    {
        return new self(
            name: $row['name'] ?? '',
            status: !empty($row['status']) ? strtolower($row['status']) : 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
