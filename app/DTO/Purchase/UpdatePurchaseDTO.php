<?php
namespace App\DTO\Purchase;

use Carbon\Carbon;

class UpdatePurchaseDTO{
    public function __construct(
        public string $purchase_date,
        public float $total,
        public string $status,
        public int $supplier_id,
        public int $user_id,
    ){
        $this->purchase_date=Carbon::createFromFormat('dmY',$purchase_date)->format('Y-m-d');
    }
}
