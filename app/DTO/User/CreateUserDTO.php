<?php
namespace App\DTO\User;

class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int $role_id
    ){}
}
