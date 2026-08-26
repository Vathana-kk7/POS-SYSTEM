<?php

namespace App\Exports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class BrandsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private array $filters = []
    ) {}

    public function query()
    {
        $query = Brand::query();

        if (!empty($this->filters['search'])) {
            $query->where('name', 'like', '%' . $this->filters['search'] . '%');
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('name')->limit(2000);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Status',
        ];
    }

    /**
     * Map each database row directly before streaming to Excel
     */
    public function map($brand): array
    {
        return [
            $brand->name,
            $brand->status,
        ];
    }
}
