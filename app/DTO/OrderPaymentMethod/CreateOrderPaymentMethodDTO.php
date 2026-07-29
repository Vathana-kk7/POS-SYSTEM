<?php
namespace App\DTO\OrderPaymentMethod;

use Carbon\Carbon;

class CreateOrderPaymentMethodDTO{
    public function __construct(
        public float $amount,
        public ?string  $transaction_id,
        public string  $payment_status,
        public ?Carbon $paid_at,
        public int $order_id,
        public int $bank_id,
    ){}
}
