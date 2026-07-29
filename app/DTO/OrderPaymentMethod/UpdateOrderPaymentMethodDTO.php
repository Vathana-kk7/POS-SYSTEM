<?php
namespace App\DTO\OrderPaymentMethod;

use Carbon\Carbon;

class UpdateOrderPaymentMethodDTO{
    public function __construct(
        public ?float $amount = null,
        public ?string $transaction_id = null,
        public ?string $payment_status = null,
        public ?Carbon $paid_at = null,
        public ?int $order_id = null,
        public ?int $bank_id = null,
    ){}
}
