<?php
namespace App\DTO\Order;
class UpdateOrderDTO{
    public function __construct(
        public float $total,
        public float $discount,
        public float $tax,
        public float $grand_total,
        public string $payment_status,
        public string $order_status,
        public int $customer_id,
    ){}
}
