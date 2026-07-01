<?php

namespace App\DOT;

class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password
    ) {}
}