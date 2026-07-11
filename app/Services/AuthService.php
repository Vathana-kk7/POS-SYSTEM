<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $repo
    ) {}

    public function register($dto)
    {
        $staffRole = \App\Models\Role::where('name', 'Staff')->first();
        $user = $this->repo->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role_id' => $staffRole->id,
        ]);

        return [
            'status' => 'success',
            'message' => 'Register successful',
            'data' => $user->load('role')
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

         $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'data' => $user
        ];
    }

    //google login auto
    public function handleGoogleLogin($socialUser)
{
    // 1. Validate Google data
    if (!$socialUser || !$socialUser->getEmail()) {
        return [
            'status' => 'error',
            'message' => 'Google account data invalid'
        ];
    }

    // 2. Prepare data
    $userData = [
        'id' => $socialUser->getId(),
        'name' => $socialUser->getName() ?? 'No Name',
        'email' => $socialUser->getEmail(),
    ];

    // 3. Create or update user
    $user = $this->repo->updateOrCreateGoogleUser($userData);

    // 4. Ensure user is valid model
    if (!$user instanceof \App\Models\User) {
        return [
            'status' => 'error',
            'message' => 'User creation failed'
        ];
    }

    // 5. Ensure Sanctum works
    if (!method_exists($user, 'createToken')) {
        return [
            'status' => 'error',
            'message' => 'Sanctum not configured properly'
        ];
    }

    // 6. Create token
    $token = $user->createToken('auth_token')->plainTextToken;

    // 7. Return response
    return [
        'status' => 'success',
        'message' => 'Login successful with Google',
        'token' => $token,
        'data' => $user
    ];
}


}
