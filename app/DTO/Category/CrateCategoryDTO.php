<?php
namespace App\DTO\Category;
class CrateCategoryDTO{
    public function __construct(
        public string $name,
        public string $description,
        public string $status,

    ){}
}
