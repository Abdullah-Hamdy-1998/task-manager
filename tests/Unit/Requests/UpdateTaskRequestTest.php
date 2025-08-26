<?php

use App\Http\Requests\UpdateTaskRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::factory()->create(['name' => 'manager']);
    $this->user = User::factory()->create(['role_id' => $role->id]);
    $this->assignee = User::factory()->create(['role_id' => $role->id]);
    Auth::login($this->user);
});

it('authorization always returns true', function () {
    $request = new UpdateTaskRequest;
    expect($request->authorize())->toBeTrue();
});

it('validation passes with empty data', function () {
    $data = [];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation passes with valid title', function () {
    $data = [
        'title' => 'Updated Task Title',
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation passes with valid description', function () {
    $data = [
        'description' => 'Updated task description',
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation passes with valid assignee id', function () {
    $data = [
        'assignee_id' => $this->assignee->id,
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation passes with valid due date', function () {
    $data = [
        'due_date' => '2024-12-31',
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation passes with all valid fields', function () {
    $data = [
        'title' => 'Updated Task Title',
        'description' => 'Updated task description',
        'assignee_id' => $this->assignee->id,
        'due_date' => '2024-12-31',
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

it('validation fails when title exceeds max length', function () {
    $data = [
        'title' => str_repeat('a', 256), // 256 characters
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('title');
});

it('validation fails when title is not string', function () {
    $data = [
        'title' => 123,
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('title');
});

it('validation fails when description is not string', function () {
    $data = [
        'description' => 123,
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('description');
});

it('validation fails when assignee id is not integer', function () {
    $data = [
        'assignee_id' => 'not-an-integer',
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('assignee_id');
});

it('validation fails when assignee id does not exist', function () {
    $data = [
        'assignee_id' => 99999, // Non-existent user ID
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('assignee_id');
});

it('validation fails when due date is invalid format', function () {
    $data = [
        'due_date' => 'invalid-date-format',
    ];

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('due_date');
});

it('validation passes with valid date format', function () {
    $validDates = [
        '2024-12-31',
        '2024-01-15',
        '2025-06-30',
    ];

    foreach ($validDates as $date) {
        $data = ['due_date' => $date];
        $request = new UpdateTaskRequest;
        $validator = Validator::make($data, $request->rules());

        expect($validator->passes())->toBeTrue("Date format '{$date}' should be valid");
    }
});

it('validation passes when fields are missing', function () {
    $data = []; // Empty data - all fields are optional with 'sometimes'

    $request = new UpdateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    expect($validator->passes())->toBeTrue();
});

// Note: Null values with 'sometimes' rules behave differently in Laravel
// In practice, API requests typically omit fields rather than sending null values
