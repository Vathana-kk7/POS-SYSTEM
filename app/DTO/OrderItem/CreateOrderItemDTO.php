<?php

namespace App\DTO\OrderItem;

class CreateOrderItemDTO
{
    public function __construct(
        public int $product_id,
        public int $order_id,
        public int $quantity,
        public int $unit_price,
        public float $discount,
        public float $subtotal,
    ) {}
}
