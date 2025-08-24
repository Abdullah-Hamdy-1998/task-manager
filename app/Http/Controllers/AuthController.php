<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $result = $this->authService->register($validated);

        return response()->json([
            'user' => $result['user'],
            'token' => $result['token'],
        ], $result['status']);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $result = $this->authService->login($validated);

        return response()->json(['token' => $result['token']], $result['status']);
    }

    public function logout()
    {
        $result = $this->authService->logout();
        return response()->json(['message' => $result['message']], $result['status']);
    }
}
