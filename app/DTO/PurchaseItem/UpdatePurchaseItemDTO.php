<?php

namespace App\DTO\PurchaseItem;

class UpdatePurchaseItemDTO
{
    public function __construct(
        public int $purchase_id,
        public int $product_id,
        public int $quantity,
        public float $cost_price,
        public float $discount,
        public float $tax,
        public float $subtotal,
    ) {}
}
