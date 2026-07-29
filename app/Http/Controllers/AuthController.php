<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\LoginDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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



    // បង្កើត Session ID ថ្មីផ្ញើទៅ Client (Postman នឹងទទួលCookie ត្រង់នេះ)
    $request->session()->regenerate();

    return response()->json([
        "status" => "success",
        "message" => $result["message"],
        "data" => $result["data"]
    ]);
}

    //Logout
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();


        return response()->json([
            'status'=>'success',
            'message'=>'Logged out successfully'
        ]);
    }


    //google
// ១. មុខងារបញ្ជូន User ទៅកាន់ Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }
// ២. មុខងារទទួលទិន្នន័យមកវិញពី Google ពេល User វាយ Email/Password ត្រូវ
   public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->user();

        $result = $this->auth->handleGoogleLogin($googleUser);

        if ($result['status'] !== 'success') {
            return redirect('http://localhost:5173/login');
        }

        session()->regenerate();

        return redirect('http://localhost:5173/auth/google/callback');

    } catch (\Exception $e) {
        return redirect('http://localhost:5173/login');
    }
}
}
