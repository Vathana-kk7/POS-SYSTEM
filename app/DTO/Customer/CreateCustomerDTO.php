<?php
namespace App\DTO\Customer;
class CreateCustomerDTO{
    public function __construct(
        public string $name,
        public string $phone,
        public string $address,
    ){}
}
