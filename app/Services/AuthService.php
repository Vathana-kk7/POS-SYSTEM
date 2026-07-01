<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// AuthService
class AuthService
{
    public function __construct(
        private UserRepositoryInterface $repo
    ) {}

    public function register($dto)
    {
        $user = $this->repo->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        return [
            'status' => 'success',
            'message' => 'Register successful',
            'data' => $user
        ];
    }

    public function login($dto)
    {
        $user = $this->repo->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            return [
                'status' => 'error',
                'message' => 'Invalid credentials'
            ];
        }

        $token = base64_encode(Str::random(40) . $user->id);

        return [
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'data' => $user
        ];
    }
}
