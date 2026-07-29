<?php
namespace App\DTO\Customer;
class UpdateCustomerDTO{
    public function __construct(
        public string $name,
        public string $phone,
        public string $address,
    ){}
}
