<?php
namespace App\DTO\Unite;
class UpdateUniteDTO{
    public function __construct(
        public string $name,
        public string $symbol,
    ){}
}
