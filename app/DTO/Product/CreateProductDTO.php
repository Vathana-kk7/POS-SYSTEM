<?php
namespace App\DTO\Product;

use Illuminate\Http\UploadedFile;

class CreateProductDTO{
    public function __construct(
        public string $name,
        public int $stock_qty,
        public float $cost_price,
        public string $description,
        public string $sku,
        public ?string $image,
        public int $min_stock_level,
        public string $status,
        public float $selling_price,
        public int $brand_id,
        public int $category_id,

    ){}
}
