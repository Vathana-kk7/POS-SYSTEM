<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{

    public function create(array $data):User
    {
        return User::create($data);
    }
    public function all()
    {
        return User::with('role')->get();
    }
    public function findById(string $id):User
    {
        return User::with('role')->findOrFail($id);
    }
    public function update(string $id, array $data): User
    {
        $user = $this->findById($id);
        $user->update($data);
        return $user->refresh();
    }
    public function delete(string $id): bool
    {
        return $this->findById($id)->delete();
    }
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    public function updateOrCreateGoogleUser(array $socialUser): User
    {
        return User::updateOrCreate(
            [
                'email' => $socialUser['email'],
            ],
            [
                'name'      => $socialUser['name'],
                'google_id' => $socialUser['id'],
                'password'  => null,
                'role_id'   => 3,
            ]
        );
    }

}
