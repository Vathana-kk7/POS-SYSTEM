<?php

namespace App\DTO\Purchase;

use Carbon\Carbon;

class UpdatePurchaseDTO
{
    public function __construct(
        public string $purchase_date,
        public int $supplier_id,
        public int $user_id,

        public ?string $expected_delivery_date = null,

        public float $discount = 0.00,
        public float $tax = 0.00,
        public float $shipping_cost = 0.00,

        public ?string $payment_terms = null,
        public ?string $notes = null,
        public string $status = 'draft',
    ) {
        $this->purchase_date = Carbon::parse(
            $purchase_date
        )->format('Y-m-d');

        if ($this->expected_delivery_date) {
            $this->expected_delivery_date = Carbon::parse(
                $this->expected_delivery_date
            )->format('Y-m-d');
        }
    }
}
