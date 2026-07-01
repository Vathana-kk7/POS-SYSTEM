<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
// UserRepository
class UserRepository implements UserRepositoryInterface
{
    public function create(array $data)
    {
        return User::create($data);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }
    public function updateOrCreateGoogleUser(array $socialUser): User
{
    return User::updateOrCreate(
        ['email' => $socialUser['email']], // បើមាន Email នេះហើយ វានឹងស្វែងរកមកប្រើ
        [
            'name' => $socialUser['name'],
            'google_id' => $socialUser['id'],
            'password' => null // មិនបាច់មានលេខសម្ងាត់ទេ
        ]
    );
}
}
