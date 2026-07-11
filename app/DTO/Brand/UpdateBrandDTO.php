<?php
namespace App\DTO\Brand;
class UpdateBrandDTO{
    public function __construct(
        public string $name,
        public string $status
    ){}
}
