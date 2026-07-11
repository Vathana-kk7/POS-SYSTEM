<?php
namespace App\DTO\Category;
class UpdateCategoryDTO{
    public function __construct(
        public string $name,
        public string $description,
    ){}
}
