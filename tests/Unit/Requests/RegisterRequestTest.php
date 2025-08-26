<?php

use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

it('authorization returns true', function () {
    $request = new RegisterRequest;
    expect($request->authorize())->toBeTrue();
});

it('validation passes with valid data', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation fails when name is missing', function () {
    $data = [
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('name');
});

it('validation fails when email is invalid', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('email');
});

it('validation fails when email already exists', function () {
    $role = Role::factory()->create();
    User::factory()->create(['email' => 'existing@example.com', 'role_id' => $role->id]);

    $data = [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('email');
});

it('validation fails when password is too short', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '123',
        'password_confirmation' => '123',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('password');
});

it('validation fails when password confirmation does not match', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('password');
});

it('validation fails when role is invalid', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'invalid_role',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('role');
});

it('validation passes with manager role', function () {
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'manager',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation fails when name exceeds max length', function () {
    $data = [
        'name' => str_repeat('a', 256), // 256 characters
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'user',
    ];

    $request = new RegisterRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('name');
});
