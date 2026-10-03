<?php
namespace App\DTO\Supplier;
class UpdateSupplierDTO{
    public function __construct(
        public string $name,
        public string $address,
        public string $phone,
        public ?string $contact_person = null,
        public ?string $email = null,
        public string $status = 'Active',
    ){}
}
