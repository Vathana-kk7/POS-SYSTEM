<?php
namespace App\DTO\Purchase;

use Carbon\Carbon;
use Ramsey\Uuid\Type\Decimal;

class CreatePurchaseDTO{
    public function __construct(
        public string $purchase_date,
        public float $total,
        public string $status,
        public int $supplier_id,
        public int $user_id,
    ){
        // បំប្លែង និងកំណត់តម្លៃទៅឱ្យ property $purchase_date ជាស្វ័យប្រវត្តិពេលបង្កើត Object
        $this->purchase_date = Carbon::createFromFormat('dmY', $purchase_date)->format('Y-m-d');
    }
}
