<?php

namespace App\DTO\GoodsReceipt;

class ReceiveGoodsDTO
{
    public function __construct(
        public int $purchase_id,
        public int $user_id,
        public array $items,
    ) {}
}
