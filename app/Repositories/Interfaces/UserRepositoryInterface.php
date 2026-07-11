<?php

namespace App\Repositories\Interfaces;
// UserRepositoryInterface
interface UserRepositoryInterface
{
    public function create(array $data);

    public function all();

    public function findById(string $id);

    public function update(string $id, array $data);

    public function delete(string $id);

    public function findByEmail(string $email);

    public function updateOrCreateGoogleUser(array $socialUser);
}
