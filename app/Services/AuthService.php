<?php

namespace App\Services;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Role;
use App\Http\Resources\UserResource;

class AuthService
{
    public function register(array $data)
    {
        $role = Role::where('name', $data['role'])->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role_id' => $role->id,
        ]);

        $token = JWTAuth::attempt(['email' => $data['email'], 'password' => $data['password']]);

        $user->load('role');
        
        return ['user' => new UserResource($user), 'token' => $token, 'status' => 201];
    }

    public function login(array $credentials)
    {
        if (! $token = JWTAuth::attempt($credentials)) {
            return ['error' => 'Invalid credentials', 'status' => 401];
        }

        return ['token' => $token, 'status' => 200];
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return ['message' => 'Successfully logged out', 'status' => 200];
    }
}