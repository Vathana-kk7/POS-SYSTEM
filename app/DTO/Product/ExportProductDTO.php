<?php

namespace App\DTO\Product;

use App\Models\Product;

class ExportProductDTO
{
    public function __construct(
        public ?string $barcode,
        public ?string $description,
        public float|int|string $sellingPrice,
        public int|string $stockQty
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            barcode: $product->barcode,
            description: $product->description,
            sellingPrice: $product->selling_price,
            stockQty: $product->stock_qty
        );
    }

    public function toArray(): array
    {
        return [
            'Barcode' => $this->barcode,
            'Description' => $this->description,
            'Selling Price' => $this->sellingPrice,
            'Stock Qty' => $this->stockQty,
        ];
    }
}
