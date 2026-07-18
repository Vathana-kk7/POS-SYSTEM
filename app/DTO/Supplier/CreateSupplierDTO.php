<?php
namespace App\DTO\Supplier;
class CreateSupplierDTO{
    public function __construct(
        public string $name,
        public string $address,
        public string $phone,
    ){}
}
