<?php

namespace App\DTO\Category;

class ImportCategoryDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly string $status = 'active',
    ) {}

    public static function fromExcelRow(array $row): self
    {
        return new self(
            name: trim($row['name'] ?? ''),
            description: isset($row['description']) ? trim($row['description']) : null,
            status: !empty($row['status']) ? strtolower(trim($row['status'])) : 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
