<?php

use App\Contracts\TaskRepositoryInterface;
use App\Exceptions\TaskIncompleteDependenciesException;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('TaskUpdateService', function () {
    beforeEach(function () {
        $this->mockRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $this->service = new TaskUpdateService($this->mockRepository);

        // Create test user for authentication
        $role = Role::create(['name' => 'test', 'description' => 'Test role']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        Auth::login($this->user);
    });

    describe('updateTask', function () {
        it('updates task and returns fresh instance', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockFreshTask = \Mockery::mock(Task::class);
            $updateData = [
                'description' => 'Updated description',
                'status' => 'in_progress',
            ];

            $this->mockRepository
                ->shouldReceive('update')
                ->once()
                ->with($mockTask, $updateData);

            $mockTask
                ->shouldReceive('fresh')
                ->once()
                ->andReturn($mockFreshTask);

            $result = $this->service->updateTask($mockTask, $updateData);

            expect($result)->toBe($mockFreshTask);
        });

        it('handles empty update data', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockFreshTask = \Mockery::mock(Task::class);
            $updateData = [];

            $this->mockRepository
                ->shouldReceive('update')
                ->once()
                ->with($mockTask, $updateData);

            $mockTask
                ->shouldReceive('fresh')
                ->once()
                ->andReturn($mockFreshTask);

            $result = $this->service->updateTask($mockTask, $updateData);

            expect($result)->toBe($mockFreshTask);
        });
    });

    describe('updateStatus', function () {
        it('updates status to pending successfully', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockFreshTask = \Mockery::mock(Task::class);
            $newStatus = 'pending';

            $this->mockRepository
                ->shouldReceive('updateStatus')
                ->once()
                ->with($mockTask, $newStatus);

            $mockTask
                ->shouldReceive('fresh')
                ->once()
                ->andReturn($mockFreshTask);

            $result = $this->service->updateStatus($mockTask, $newStatus);

            expect($result)->toBe($mockFreshTask);
        });

        it('updates status to in_progress successfully', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockFreshTask = \Mockery::mock(Task::class);
            $newStatus = 'in_progress';

            $this->mockRepository
                ->shouldReceive('updateStatus')
                ->once()
                ->with($mockTask, $newStatus);

            $mockTask
                ->shouldReceive('fresh')
                ->once()
                ->andReturn($mockFreshTask);

            $result = $this->service->updateStatus($mockTask, $newStatus);

            expect($result)->toBe($mockFreshTask);
        });

        it('updates status to completed when no incomplete dependencies', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockFreshTask = \Mockery::mock(Task::class);
            $newStatus = 'completed';

            $mockTask
                ->shouldReceive('hasIncompleteDependencies')
                ->once()
                ->andReturn(false);

            $this->mockRepository
                ->shouldReceive('updateStatus')
                ->once()
                ->with($mockTask, $newStatus);

            $mockTask
                ->shouldReceive('fresh')
                ->once()
                ->andReturn($mockFreshTask);

            $result = $this->service->updateStatus($mockTask, $newStatus);

            expect($result)->toBe($mockFreshTask);
        });

        it('throws exception when completing task with incomplete dependencies', function () {
            $mockTask = \Mockery::mock(Task::class);
            $newStatus = 'completed';

            $mockTask
                ->shouldReceive('hasIncompleteDependencies')
                ->once()
                ->andReturn(true);

            expect(fn () => $this->service->updateStatus($mockTask, $newStatus))
                ->toThrow(TaskIncompleteDependenciesException::class);
        });

        it('updates status to cancelled successfully', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockFreshTask = \Mockery::mock(Task::class);
            $newStatus = 'cancelled';

            $this->mockRepository
                ->shouldReceive('updateStatus')
                ->once()
                ->with($mockTask, $newStatus);

            $mockTask
                ->shouldReceive('fresh')
                ->once()
                ->andReturn($mockFreshTask);

            $result = $this->service->updateStatus($mockTask, $newStatus);

            expect($result)->toBe($mockFreshTask);
        });
    });

    afterEach(function () {
        \Mockery::close();
    });
});
