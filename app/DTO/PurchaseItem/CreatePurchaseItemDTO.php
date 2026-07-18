<?php
namespace App\DTO\PurchaseItem;
class CreatePurchaseItemDTO{
    public function __construct(
        public int $purchase_id,
        public int $product_id,
        public float $quantity,
        public float $cost_price,
        public float $subtotal,
    ){}
}
