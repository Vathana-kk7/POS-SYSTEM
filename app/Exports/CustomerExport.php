<?php

namespace App\Exports;

use App\Repositories\Interfaces\CustomerRepositoryInterface;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private CustomerRepositoryInterface $customers,
        private array $filters = [],
    ) {}

    public function collection()
    {
        return $this->customers->getForExport($this->filters);
    }

    public function headings(): array
    {
        return ['Name', 'Phone', 'Email', 'Address', 'Group', 'Status'];
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->phone,
            $customer->email,
            $customer->address,
            $customer->group,
            $customer->status,
        ];
    }
}
