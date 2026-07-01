<?php

namespace App\DOT;
// DTO
class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}
}
