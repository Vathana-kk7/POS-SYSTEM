<?php
namespace App\DTO\Supplier;
class UpdateSupplierDTO{
    public function __construct(
        public string $name,
        public string $address,
        public string $phone,
    ){}
}
