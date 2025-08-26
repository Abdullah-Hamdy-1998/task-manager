<?php

use App\Http\Requests\CreateTaskRequest;
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
});

it('authorize returns true when user can create tasks', function () {
    // Arrange
    Auth::login($this->user);
    $request = new CreateTaskRequest;
    $request->setUserResolver(function () {
        return $this->user;
    });

    // Act & Assert
    expect($request->authorize())->toBeTrue();
});

it('validation passes with valid minimal data', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeTrue();
});

it('validation passes with valid complete data', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'description' => 'This is a test task description',
        'assignee_id' => $this->assignee->id,
        'due_date' => '2024-12-31',
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeTrue();
});

it('validation fails when title is missing', function () {
    // Arrange
    $data = [
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('title');
});

it('validation fails when status is missing', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('status');
});

it('validation fails when title exceeds max length', function () {
    // Arrange
    $data = [
        'title' => str_repeat('a', 256), // 256 characters
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('title');
});

it('validation fails when status is invalid', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'status' => 'invalid_status',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('status');
});

it('validation fails when assignee id does not exist', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'assignee_id' => 99999, // Non-existent user ID
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('assignee_id');
});

it('validation fails when due date is invalid format', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'due_date' => 'invalid-date',
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeFalse();
    expect($validator->errors()->toArray())->toHaveKey('due_date');
});

it('validation passes with all valid status values', function () {
    // Arrange
    $validStatuses = ['pending', 'completed', 'canceled'];

    foreach ($validStatuses as $status) {
        $data = [
            'title' => 'Test Task',
            'status' => $status,
        ];

        $request = new CreateTaskRequest;
        $validator = Validator::make($data, $request->rules());

        // Act & Assert
        expect($validator->passes())->toBeTrue("Status '{$status}' should be valid");
    }
});

it('validation passes when optional fields are null', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'description' => null,
        'assignee_id' => null,
        'due_date' => null,
        'status' => 'pending',
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeTrue();
});

it('validation passes when optional fields are omitted', function () {
    // Arrange
    $data = [
        'title' => 'Test Task',
        'status' => 'pending',
        // description, assignee_id, and due_date are omitted
    ];

    $request = new CreateTaskRequest;
    $validator = Validator::make($data, $request->rules());

    // Act & Assert
    expect($validator->passes())->toBeTrue();
});
