<?php

use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

describe('AuthService', function () {
    beforeEach(function () {
        $this->service = new AuthService;

        // Create test roles
        $this->userRole = Role::create(['name' => 'user', 'description' => 'User role']);
        $this->managerRole = Role::create(['name' => 'manager', 'description' => 'Manager role']);
    });

    describe('register', function () {
        it('registers user successfully with user role', function () {
            $userData = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'role' => 'user',
            ];

            $mockToken = 'mock.jwt.token';

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with(['email' => $userData['email'], 'password' => $userData['password']])
                ->andReturn($mockToken);

            $result = $this->service->register($userData);

            expect($result)
                ->toHaveKey('user')
                ->toHaveKey('token')
                ->toHaveKey('status')
                ->and($result['token'])->toBe($mockToken)
                ->and($result['status'])->toBe(201)
                ->and($result['user'])->toBeInstanceOf(UserResource::class);

            // Verify user was created in database
            $this->assertDatabaseHas('users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'role_id' => $this->userRole->id,
            ]);
        });

        it('registers user successfully with manager role', function () {
            $userData = [
                'name' => 'Jane Manager',
                'email' => 'jane@example.com',
                'password' => 'password123',
                'role' => 'manager',
            ];

            $mockToken = 'mock.jwt.token';

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with(['email' => $userData['email'], 'password' => $userData['password']])
                ->andReturn($mockToken);

            $result = $this->service->register($userData);

            expect($result)
                ->toHaveKey('user')
                ->toHaveKey('token')
                ->toHaveKey('status')
                ->and($result['token'])->toBe($mockToken)
                ->and($result['status'])->toBe(201);

            // Verify user was created with correct role
            $this->assertDatabaseHas('users', [
                'name' => 'Jane Manager',
                'email' => 'jane@example.com',
                'role_id' => $this->managerRole->id,
            ]);
        });

        it('encrypts password during registration', function () {
            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'plainpassword',
                'role' => 'user',
            ];

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->andReturn('mock.token');

            $this->service->register($userData);

            $user = User::where('email', 'test@example.com')->first();

            expect($user->password)
                ->not()->toBe('plainpassword')
                ->and(Hash::check('plainpassword', $user->password))->toBeTrue();
        });

        it('loads user role relationship', function () {
            $userData = [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'role' => 'user',
            ];

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->andReturn('mock.token');

            $result = $this->service->register($userData);

            // The UserResource should have access to the role relationship
            expect($result['user'])->toBeInstanceOf(UserResource::class);
        });
    });

    describe('login', function () {
        it('returns token on successful login', function () {
            $credentials = [
                'email' => 'user@example.com',
                'password' => 'password123',
            ];

            $mockToken = 'mock.jwt.token';

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with($credentials)
                ->andReturn($mockToken);

            $result = $this->service->login($credentials);

            expect($result)
                ->toHaveKey('token')
                ->toHaveKey('status')
                ->and($result['token'])->toBe($mockToken)
                ->and($result['status'])->toBe(200);
        });

        it('returns error on invalid credentials', function () {
            $credentials = [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ];

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with($credentials)
                ->andReturn(false);

            $result = $this->service->login($credentials);

            expect($result)
                ->toHaveKey('error')
                ->toHaveKey('status')
                ->and($result['error'])->toBe('Invalid credentials')
                ->and($result['status'])->toBe(401);
        });

        it('handles empty credentials', function () {
            $credentials = [];

            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with($credentials)
                ->andReturn(false);

            $result = $this->service->login($credentials);

            expect($result)
                ->toHaveKey('error')
                ->toHaveKey('status')
                ->and($result['error'])->toBe('Invalid credentials')
                ->and($result['status'])->toBe(401);
        });
    });

    describe('logout', function () {
        it('invalidates token and returns success message', function () {
            $mockToken = 'mock.jwt.token';

            JWTAuth::shouldReceive('getToken')
                ->once()
                ->andReturn($mockToken);

            JWTAuth::shouldReceive('invalidate')
                ->once()
                ->with($mockToken);

            $result = $this->service->logout();

            expect($result)
                ->toHaveKey('message')
                ->toHaveKey('status')
                ->and($result['message'])->toBe('Successfully logged out')
                ->and($result['status'])->toBe(200);
        });

        it('handles JWT exceptions during logout', function () {
            JWTAuth::shouldReceive('getToken')
                ->once()
                ->andThrow(new JWTException('Token not provided'));

            expect(fn () => $this->service->logout())
                ->toThrow(JWTException::class, 'Token not provided');
        });
    });

    describe('integration tests', function () {
        it('complete registration and login flow', function () {
            // First register a user
            $userData = [
                'name' => 'Integration Test User',
                'email' => 'integration@example.com',
                'password' => 'password123',
                'role' => 'user',
            ];

            // Mock JWT for registration
            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with(['email' => $userData['email'], 'password' => $userData['password']])
                ->andReturn('registration.token');

            $registerResult = $this->service->register($userData);

            expect($registerResult['status'])->toBe(201);

            // Then try to login with the same credentials
            $credentials = [
                'email' => $userData['email'],
                'password' => $userData['password'],
            ];

            // Mock JWT for login
            JWTAuth::shouldReceive('attempt')
                ->once()
                ->with($credentials)
                ->andReturn('login.token');

            $loginResult = $this->service->login($credentials);

            expect($loginResult['status'])->toBe(200)
                ->and($loginResult['token'])->toBe('login.token');
        });
    });

    afterEach(function () {
        \Mockery::close();
    });
});
