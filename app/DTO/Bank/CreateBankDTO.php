<?php
namespace App\DTO\Bank;

class CreateBankDTO{
    public function __construct(
        public string $name,
        public string  $account_name,
        public string  $account_number,
        public string $qr_code,
        public string $status,
    ){}
}
