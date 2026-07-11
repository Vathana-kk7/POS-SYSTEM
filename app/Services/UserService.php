<?php

namespace App\Services;
use App\DTO\User\UpdateUserDTO;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $repo
    ) {}


    public function createUser($dto)
    {
        $user = $this->repo->create([
            "name" => $dto->name,
            "email" => $dto->email,
            "password" => Hash::make($dto->password),
            "role_id" => $dto->role_id,
        ]);

        return $user->load('role');
    }
    public function getAllUsers()
    {
        return $this->repo->all();
    }
    public function getUserById($id)
    {
        return $this->repo->findById($id);
    }
    public function updateUser($id, UpdateUserDTO $dto)
    {
        $data = [
            'name'=> $dto->name,
            'email'=> $dto->email,
            'role_id'=> $dto->role_id,
        ];
        if ($dto->password) {
            $data['password'] = Hash::make($dto->password);
        }
        return $this->repo->update($id,$data)->load('role');
    }
    public function deleteUser($id)
    {
        return $this->repo->delete($id);
    }
}
