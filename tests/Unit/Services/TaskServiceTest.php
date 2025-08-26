<?php

use App\Contracts\TaskRepositoryInterface;
use App\Exceptions\TaskNotFoundException;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('TaskService', function () {
    beforeEach(function () {
        $this->mockRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $this->service = new TaskService($this->mockRepository);

        // Create test user for authentication
        $role = Role::create(['name' => 'test', 'description' => 'Test role']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        Auth::login($this->user);
    });

    describe('getAllTasks', function () {
        it('returns paginated tasks with default pagination', function () {
            $mockPaginator = \Mockery::mock(LengthAwarePaginator::class);

            $this->mockRepository
                ->shouldReceive('getFilteredTasks')
                ->once()
                ->with([], 15)
                ->andReturn($mockPaginator);

            $result = $this->service->getAllTasks();

            expect($result)->toBe($mockPaginator);
        });

        it('returns paginated tasks with custom filters and pagination', function () {
            $filters = ['status' => 'pending'];
            $perPage = 10;
            $mockPaginator = \Mockery::mock(LengthAwarePaginator::class);

            $this->mockRepository
                ->shouldReceive('getFilteredTasks')
                ->once()
                ->with($filters, $perPage)
                ->andReturn($mockPaginator);

            $result = $this->service->getAllTasks($filters, $perPage);

            expect($result)->toBe($mockPaginator);
        });
    });

    describe('getMyTasks', function () {
        it('returns user tasks with default pagination', function () {
            $mockPaginator = \Mockery::mock(LengthAwarePaginator::class);

            $this->mockRepository
                ->shouldReceive('getUserTasks')
                ->once()
                ->with($this->user, [], 15)
                ->andReturn($mockPaginator);

            $result = $this->service->getMyTasks($this->user);

            expect($result)->toBe($mockPaginator);
        });

        it('returns user tasks with custom filters and pagination', function () {
            $filters = ['status' => 'completed'];
            $perPage = 20;
            $mockPaginator = \Mockery::mock(LengthAwarePaginator::class);

            $this->mockRepository
                ->shouldReceive('getUserTasks')
                ->once()
                ->with($this->user, $filters, $perPage)
                ->andReturn($mockPaginator);

            $result = $this->service->getMyTasks($this->user, $filters, $perPage);

            expect($result)->toBe($mockPaginator);
        });
    });

    describe('getTask', function () {
        it('returns task when found', function () {
            $taskId = 1;
            $mockTask = \Mockery::mock(Task::class);

            $this->mockRepository
                ->shouldReceive('find')
                ->once()
                ->with($taskId)
                ->andReturn($mockTask);

            $result = $this->service->getTask($taskId);

            expect($result)->toBe($mockTask);
        });

        it('returns null when task not found', function () {
            $taskId = 999;

            $this->mockRepository
                ->shouldReceive('find')
                ->once()
                ->with($taskId)
                ->andReturn(null);

            $result = $this->service->getTask($taskId);

            expect($result)->toBeNull();
        });
    });

    describe('getTaskOrFail', function () {
        it('returns task when found', function () {
            $taskId = 1;
            $mockTask = \Mockery::mock(Task::class);

            $this->mockRepository
                ->shouldReceive('find')
                ->once()
                ->with($taskId)
                ->andReturn($mockTask);

            $result = $this->service->getTaskOrFail($taskId);

            expect($result)->toBe($mockTask);
        });

        it('throws TaskNotFoundException when task not found', function () {
            $taskId = 999;

            $this->mockRepository
                ->shouldReceive('find')
                ->once()
                ->with($taskId)
                ->andReturn(null);

            expect(fn () => $this->service->getTaskOrFail($taskId))
                ->toThrow(TaskNotFoundException::class);
        });
    });

    afterEach(function () {
        \Mockery::close();
    });
});
