<?php
namespace App\DTO\Supplier;

use Illuminate\Http\UploadedFile;

class UpdateSupplierDTO{
    public function __construct(
        public string $name,
        public string $address,
        public string $phone,
        public ?string $contact_person = null,
        public ?string $email = null,
        public string $status = 'Active',
        public ?string $bank_name = null,
        public ?string $account_name = null,
        public ?string $account_number = null,
        public ?string $bakong_account_id = null,
        public ?UploadedFile $khqr_image = null,
    ){}
}
