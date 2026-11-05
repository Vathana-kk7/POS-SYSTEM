<?php

namespace App\DTO\Purchase;

class ReceivePurchaseDTO
{
    public function __construct(
        public int $purchase_id,
        public array $items,
        public int $user_id,
    ) {}
}
