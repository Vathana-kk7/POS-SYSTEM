<?php

namespace App\DTO\Category;

class ImportCategoryDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public mixed $status,
    ) {}

    public static function fromExcelRow(array $row): self
    {
        // បម្លែង "Active" -> 1 / "Inactive" -> 0
        $status = isset($row['status']) && strtolower(trim($row['status'])) === 'active' ? 1 : 0;

        return new self(
            name: trim($row['name'] ?? ''),
            description: isset($row['description']) ? trim($row['description']) : null,
            status: $status,
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
