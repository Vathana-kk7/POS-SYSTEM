<?php

namespace App\DTO\Auth;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {}
}
