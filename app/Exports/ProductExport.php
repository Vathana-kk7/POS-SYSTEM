<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private array $filters = []
    ) {}

    public function query()
    {
        $query = Product::query();

        // Search
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Status
        if (
            !empty($this->filters['status']) &&
            $this->filters['status'] !== 'all'
        ) {
            $query->where('status', $this->filters['status']);
        }

        return $query
            ->orderByDesc('id')
            ->limit(5000);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'SKU',
            'Barcode',
            'Description',
            'Selling Price',
            'Cost Price',
            'Stock Qty',
            'Min Stock Level',
            'Status',
        ];
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->sku,
            $product->barcode,
            $product->description,
            $product->selling_price,
            $product->cost_price,
            $product->stock_qty,
            $product->min_stock_level,
            $product->status,
        ];
    }
}
