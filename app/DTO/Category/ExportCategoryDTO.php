<?php
namespace App\DTO\Category;

use App\Models\Category;

class ExportCategoryDTO{
    public function __construct(
        public string $name,
        public string $status,
        public string $description,

    ){}
    public static function fromModel(Category $category):self
    {
        return new self(
            name:$category->name,
            status:$category->status,
            description:$category->description,
        );
    }

    public function toArray():array
    {
        return [
            'name'=>$this->name,
            'status'=>$this->status,
            'description'=>$this->description,
        ];
    }
}
