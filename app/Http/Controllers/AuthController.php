<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;

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

        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], $result['status']);
        }

        return response()->json(['token' => $result['token']], $result['status']);
    }

    public function logout()
    {
        $result = $this->authService->logout();

        return response()->json(['message' => $result['message']], $result['status']);
    }
}
