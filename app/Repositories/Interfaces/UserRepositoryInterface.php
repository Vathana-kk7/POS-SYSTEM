<?php

namespace App\Repositories\Interfaces;
// UserRepositoryInterface
interface UserRepositoryInterface
{
    public function create(array $data);
    public function findByEmail(string $email);
    public function updateOrCreateGoogleUser(array $socialUser);
}
