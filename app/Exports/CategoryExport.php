<?php

namespace App\Exports;

use App\Models\Category;
use App\DTO\Category\ExportCategoryDTO;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoryExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private array $filters = []
    ) {}

    public function query()
    {
        $query = Category::query();

        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', '%' . trim($this->filters['search']) . '%');
        }

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('name')->limit(2000);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Status',
            'Description',
        ];
    }

    /**
     * Map Category model using ExportCategoryDTO
     */
    public function map($category): array
    {
        $dto = ExportCategoryDTO::fromModel($category);

        return [
            $dto->name,
            $dto->status,
            $dto->description,
        ];
    }
}
