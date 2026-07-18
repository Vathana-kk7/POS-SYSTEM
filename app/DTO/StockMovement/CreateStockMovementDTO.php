<?php
namespace App\DTO\StockMovement;
class CreateStockMovementDTO{
    public function __construct(
        public int $product_id,
        public int $user_id,
        public string $type,
        public int $quantity,
        public int $stock_before,
        public int $stock_after,
        public string $reference_type,
        public string $reference_id,
    ){}
}
