<?php

use App\Http\Controllers\AuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

beforeEach(function () {
    $this->authService = \Mockery::mock(AuthService::class);
    $this->controller = new AuthController($this->authService);
});

afterEach(function () {
    \Mockery::close();
});

it('returns successful response on register', function () {
    // Arrange
    $requestData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'role_id' => 2,
    ];

    $serviceResult = [
        'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
        'token' => 'jwt-token-here',
        'status' => 201,
    ];

    $request = \Mockery::mock(RegisterRequest::class);
    $request->shouldReceive('validated')->once()->andReturn($requestData);

    $this->authService
        ->shouldReceive('register')
        ->once()
        ->with($requestData)
        ->andReturn($serviceResult);

    // Act
    $response = $this->controller->register($request);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(201);

    $responseData = json_decode($response->getContent(), true);
    expect($responseData)->toHaveKey('user');
    expect($responseData)->toHaveKey('token');
    expect($responseData['user'])->toBe($serviceResult['user']);
    expect($responseData['token'])->toBe($serviceResult['token']);
});

it('returns successful response on login', function () {
    // Arrange
    $requestData = [
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    $serviceResult = [
        'token' => 'jwt-token-here',
        'status' => 200,
    ];

    $request = \Mockery::mock(LoginRequest::class);
    $request->shouldReceive('validated')->once()->andReturn($requestData);

    $this->authService
        ->shouldReceive('login')
        ->once()
        ->with($requestData)
        ->andReturn($serviceResult);

    // Act
    $response = $this->controller->login($request);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);

    $responseData = json_decode($response->getContent(), true);
    expect($responseData)->toHaveKey('token');
    expect($responseData['token'])->toBe($serviceResult['token']);
});

it('returns error response for invalid credentials', function () {
    // Arrange
    $requestData = [
        'email' => 'john@example.com',
        'password' => 'wrong-password',
    ];

    $serviceResult = [
        'error' => 'Invalid credentials',
        'status' => 401,
    ];

    $request = \Mockery::mock(LoginRequest::class);
    $request->shouldReceive('validated')->once()->andReturn($requestData);

    $this->authService
        ->shouldReceive('login')
        ->once()
        ->with($requestData)
        ->andReturn($serviceResult);

    // Act
    $response = $this->controller->login($request);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(401);

    $responseData = json_decode($response->getContent(), true);
    expect($responseData)->toHaveKey('error');
    expect($responseData['error'])->toBe($serviceResult['error']);
});

it('returns successful response on logout', function () {
    // Arrange
    $serviceResult = [
        'message' => 'Successfully logged out',
        'status' => 200,
    ];

    $this->authService
        ->shouldReceive('logout')
        ->once()
        ->andReturn($serviceResult);

    // Act
    $response = $this->controller->logout();

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);

    $responseData = json_decode($response->getContent(), true);
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toBe($serviceResult['message']);
});

it('handles service errors on logout', function () {
    // Arrange
    $serviceResult = [
        'message' => 'Token not found',
        'status' => 400,
    ];

    $this->authService
        ->shouldReceive('logout')
        ->once()
        ->andReturn($serviceResult);

    // Act
    $response = $this->controller->logout();

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(400);

    $responseData = json_decode($response->getContent(), true);
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toBe($serviceResult['message']);
});
