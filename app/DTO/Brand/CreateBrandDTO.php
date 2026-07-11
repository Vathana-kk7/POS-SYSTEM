<?php
namespace App\DTO\Brand;
class CreateBrandDTO{
    public function __construct(
        public string $name,
        public string $status,
    ){}
}
