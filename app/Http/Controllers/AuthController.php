<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\LoginDTO;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
//AuthController

class AuthController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->auth->register(
            new RegisterDTO(
                $request->name,
                $request->email,
                $request->password
            )
        );

        return response()->json($result, 201);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->auth->login(
            new LoginDTO(
                $request->email,
                $request->password
            )
        );

        if ($result['status'] === 'error') {
            return response()->json($result, 401);
        }

        return response()->json($result, 200);
    }

    //Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }


    //google
// ១. មុខងារបញ្ជូន User ទៅកាន់ Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
// ២. មុខងារទទួលទិន្នន័យមកវិញពី Google ពេល User វាយ Email/Password ត្រូវ
    public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $result = $this->auth->handleGoogleLogin($googleUser);

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong with Google Login',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
