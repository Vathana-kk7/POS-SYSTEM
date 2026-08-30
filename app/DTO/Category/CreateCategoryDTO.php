<?php

namespace App\DTO\Category;

class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public string $description,
        public string $status,
    ) {}
}
