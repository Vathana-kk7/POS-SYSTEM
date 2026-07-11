<?php

namespace App\Http\Controllers;

use App\DTO\User\CreateUserDTO;
use App\DTO\User\UpdateUserDTO; // ១. បានបន្ថែម use ត្រង់នេះ
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Services\UserService;

class UserController extends Controller
{
    //Dependency Injection
    public function __construct(
        private UserService $userService
    ){}

    // GET ALL USERS
    public function index()
    {
        try {
            $users = $this->userService->getAllUsers();

            return response()->json([
                "status" => "success",
                "data" => $users
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }

    // CREATE USER
    public function store(StoreUserRequest $request)
    {
        try {
            $result = $this->userService->createUser(
                new CreateUserDTO(
                    $request->name,
                    $request->email,
                    $request->password,
                    $request->role_id
                )
            );

            return response()->json([
                "status" => "success",
                "message" => "Create Successfully",
                "data" => $result
            ], 201);
        } catch(\Throwable $th){
            return response()->json([
                "status"=>"Error Create User",
                "message" => $th->getMessage()
            ], 500);
        }
    }

    // GET ONE USER
    public function show(string $id)
    {
        try {
            $user = $this->userService->getUserById($id);

            return response()->json([
                "status" => "success",
                "data" => $user
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }

    // UPDATE USER
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            // កែសម្រួលឱ្យហៅ $this->userService ដូចគ្នាទាំងអស់
            $result = $this->userService->updateUser(
                $id,
                new UpdateUserDTO(
                    $request->name,
                    $request->email,
                    $request->password,
                    (int) $request->role_id
                )
            );

            return response()->json([
                "status" => "success",
                "message" => "Update Successfully",
                "data" => $result
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    // DELETE USER
    public function destroy(string $id)
    {
        try {
            $this->userService->deleteUser($id);

            return response()->json([
                "status" => "success",
                "message" => "User deleted"
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "message" => $th->getMessage()
            ], 500);
        }
    }
}
