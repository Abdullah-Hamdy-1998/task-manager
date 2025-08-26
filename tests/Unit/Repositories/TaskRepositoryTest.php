<?php

use App\Contracts\TaskFilterServiceInterface;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles first
    $this->managerRole = Role::create(['name' => 'manager', 'description' => 'Manager role']);
    $this->userRole = Role::create(['name' => 'user', 'description' => 'User role']);

    // Create users with roles
    $this->creator = User::factory()->create(['role_id' => $this->managerRole->id]);
    $this->assignee = User::factory()->create(['role_id' => $this->userRole->id]);

    // Authenticate the creator for the TaskObserver
    Auth::login($this->creator);

    // Mock the filter service
    $this->mockFilterService = \Mockery::mock(TaskFilterServiceInterface::class);
    $this->repository = new TaskRepository($this->mockFilterService);
});

afterEach(function () {
    \Mockery::close();
});

describe('TaskRepository', function () {
    describe('create', function () {
        it('can create a task', function () {
            $taskData = [
                'title' => 'Test Task',
                'description' => 'Test Description',
                'status' => 'pending',
                'due_date' => '2024-12-31',
                'assignee_id' => $this->assignee->id,
            ];

            $task = $this->repository->create($taskData);

            expect($task)->toBeInstanceOf(Task::class)
                ->and($task->title)->toBe('Test Task')
                ->and($task->description)->toBe('Test Description')
                ->and($task->status)->toBe('pending')
                ->and($task->created_by)->toBe($this->creator->id)
                ->and($task->assignee_id)->toBe($this->assignee->id);
        });
    });

    describe('find', function () {
        it('can find a task by id', function () {
            $task = Task::factory()->create([
                'assignee_id' => $this->assignee->id,
            ]);

            $foundTask = $this->repository->find($task->id);

            expect($foundTask)->toBeInstanceOf(Task::class)
                ->and($foundTask->id)->toBe($task->id);
        });

        it('returns null for non-existent task', function () {
            $foundTask = $this->repository->find(999);

            expect($foundTask)->toBeNull();
        });
    });

    describe('update', function () {
        it('can update a task', function () {
            $task = Task::factory()->create([
                'assignee_id' => $this->assignee->id,
                'title' => 'Original Title',
            ]);

            $updateData = [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
            ];

            $result = $this->repository->update($task, $updateData);

            expect($result)->toBeTrue();

            $task->refresh();
            expect($task->title)->toBe('Updated Title')
                ->and($task->description)->toBe('Updated Description');
        });
    });

    describe('updateStatus', function () {
        it('can update task status', function () {
            $task = Task::factory()->create([
                'assignee_id' => $this->assignee->id,
                'status' => 'pending',
            ]);

            $result = $this->repository->updateStatus($task, 'completed');

            expect($result)->toBeTrue();

            $task->refresh();
            expect($task->status)->toBe('completed');
        });
    });
});
