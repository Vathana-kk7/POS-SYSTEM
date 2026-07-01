<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\DOT\RegisterDTO;
use App\DOT\LoginDTO;
use Laravel\Socialite\Facades\Socialite;
//AuthController
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $auth
    ) {}

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
        // ទាញយកទិន្នន័យ User ពី Google
        $googleUser = Socialite::driver('google')->stateless()->user();

        //  កូដថ្មីដែលត្រូវកែ៖
        $result = $this->auth->handleGoogleLogin($googleUser);

        return response()->json([
            'message' => 'Login successfully with Google',
            'data' => $result
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong with Google Login',
            'message' => $e->getMessage()
        ], 500);
    }
}
}
