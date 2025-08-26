<?php

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;

it('authorization returns true', function () {
    $request = new LoginRequest;
    expect($request->authorize())->toBeTrue();
});

it('validation passes with valid data', function () {
    $data = [
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation fails when email is missing', function () {
    $data = [
        'password' => 'password123',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('email');
});

it('validation fails when password is missing', function () {
    $data = [
        'email' => 'john@example.com',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('password');
});

it('validation fails when email is invalid format', function () {
    $data = [
        'email' => 'invalid-email-format',
        'password' => 'password123',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('email');
});

it('validation fails when email is empty string', function () {
    $data = [
        'email' => '',
        'password' => 'password123',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('email');
});

it('validation fails when password is empty string', function () {
    $data = [
        'email' => 'john@example.com',
        'password' => '',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('password');
});

it('validation passes with minimal valid data', function () {
    $data = [
        'email' => 'a@b.c',
        'password' => 'x',
    ];

    $request = new LoginRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});
