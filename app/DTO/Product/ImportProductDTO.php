<?php

namespace App\DTO\Product;

class ImportProductDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $sku,
        public readonly int $stock_qty,
        public readonly float $cost_price,
        public readonly float $selling_price,
        public readonly int $min_stock_level,
        public readonly string $product_type,

        // These IDs come from database lookup in ProductService
        public readonly int $brand_id,
        public readonly int $category_id,
        // Image filename from Excel
        public readonly ?string $image = null,
        public readonly ?string $description = null,
        public readonly string $status = 'active',

    ) {}

    /**
     * Create DTO from Excel row.
     *
     * Excel should contain:
     * brand   => ASUS
     * category => Computer
     *
     * brand_id and category_id are passed from ProductService
     * after finding the related records.
     */
    public static function fromExcelRow(
        array $row,
        int $brandId,
        int $categoryId
    ): self {
        return new self(
            name: trim($row['name'] ?? ''),

            sku: trim($row['sku'] ?? ''),

            stock_qty: (int) ($row['stock_qty'] ?? 0),

            cost_price: (float) ($row['cost_price'] ?? 0),

            selling_price: (float) ($row['selling_price'] ?? 0),

            min_stock_level: (int) ($row['min_stock_level'] ?? 0),

            product_type: trim($row['product_type'] ?? ''),

            // Resolved from database
            brand_id: $brandId,

            // Resolved from database
            category_id: $categoryId,
            // Read image filename from Excel
            image: !empty($row['image']) ? trim($row['image']) : null,
            description: !empty($row['description'])
                ? trim($row['description'])
                : null,

            status: !empty($row['status'])
                ? strtolower(trim($row['status']))
                : 'active',
        );
    }

    /**
     * Convert DTO to database array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'stock_qty' => $this->stock_qty,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'min_stock_level' => $this->min_stock_level,
            'product_type' => $this->product_type,


            // Database Foreign Keys
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'image' => $this->image,
            'description' => $this->description,
            'status' => $this->status,

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

