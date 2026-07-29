<?php
namespace App\DTO\Unite;
class CreateUniteDTO{
    public function __construct(
        public string $name,
        public string $symbol,
    ){}
}
