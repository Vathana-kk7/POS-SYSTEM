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

    public function handleGoogleLogin($socialUser)
    {
        // រៀបចំទិន្នន័យជា Array ផ្ញើទៅកាន់ Repository
        $userData = [
            'id' => $socialUser->getId(),
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
        ];

        // ហៅទៅកាន់ Repository ដើម្បីរក្សាទុក ឬ Update ទិន្នន័យ
        $user = $this->repo->updateOrCreateGoogleUser($userData);

        // ប្តូរការបង្កើត Token ឱ្យដូចទៅនឹងមុខងារ login() ធម្មតាវិញដើម្បីភាពស៊ីសង្វាក់គ្នា
        $token = base64_encode(Str::random(40) . $user->id);

        return [
            'status' => 'success',
            'message' => 'Login successful with Google',
            'token' => $token,
            'data' => $user
        ];
    }
}
