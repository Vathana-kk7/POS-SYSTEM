<?php
namespace App\DTO\Customer;
class CreateCustomerDTO{
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $address,
        public ?string $email = null,
        public ?string $group = 'Standard',
        public ?string $status = 'Active',
    ){}
}
