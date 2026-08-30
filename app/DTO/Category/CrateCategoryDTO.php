<?php
namespace App\DTO\Category;

class CrateCategoryDTO extends CreateCategoryDTO
{
    public function __construct(
        string $name,
        string $description,
        string $status,
    ) {
        parent::__construct($name, $description, $status);
    }
}
