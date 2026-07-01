<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\DOT\RegisterDTO;
use App\DOT\LoginDTO;
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
}
