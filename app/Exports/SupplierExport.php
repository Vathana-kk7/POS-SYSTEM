<?php

namespace App\Exports;

use App\Repositories\Interfaces\SupplierRepositoryInterface;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SupplierExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private SupplierRepositoryInterface $suppliers,
        private array $filters = [],
    ) {}

    public function collection()
    {
        return $this->suppliers->getForExport($this->filters);
    }

    public function headings(): array
    {
        return ['Supplier Name', 'Contact Person', 'Email', 'Phone', 'Address', 'Status', 'Total Payable'];
    }

    public function map($supplier): array
    {
        return [
            $supplier->name,
            $supplier->contact_person,
            $supplier->email,
            $supplier->phone,
            $supplier->address,
            $supplier->status,
            (float) $supplier->total_payable,
        ];
    }
}
