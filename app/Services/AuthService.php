<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

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
                'status'=>'error',
                'message'=>'Invalid credentials'
            ];
        }


        auth()->guard('web')->login($user);


        return [
            'status'=>'success',
            'message'=>'Login successful',
            'data'=>$user->load('role')
        ];
    }



    public function handleGoogleLogin($socialUser)
    {

        if (!$socialUser || !$socialUser->getEmail()) {

            return [
                'status' => 'error',
                'message' => 'Google account invalid'
            ];
        }


        $userData = [
            'google_id' => $socialUser->getId(),
            'name' => $socialUser->getName() ?? 'No Name',
            'email' => $socialUser->getEmail(),
        ];


        $user = $this->repo->updateOrCreateGoogleUser($userData);


        // Login using Session Cookie
        auth()->guard('web')->login($user);

        request()->session()->regenerate();

        return [
            'status' => 'success',
            'message' => 'Google login successful',
            'data' => $user->load('role')
        ];
    }



    public function logout()
    {
        auth()->guard('web')->logout();


        request()->session()->invalidate();

        request()->session()->regenerateToken();


        return [
            'status' => 'success',
            'message' => 'Logout successful'
        ];
    }
}
