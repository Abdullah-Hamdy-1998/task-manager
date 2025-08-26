<?php

use App\Contracts\TaskCycleServiceInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Exceptions\TaskAlreadyCompletedException;
use App\Exceptions\TaskCycleDependencyException;
use App\Exceptions\TaskDuplicateDependencyException;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskCreationService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('TaskCreationService', function () {
    beforeEach(function () {
        $this->mockRepository = \Mockery::mock(TaskRepositoryInterface::class);
        $this->mockCycleService = \Mockery::mock(TaskCycleServiceInterface::class);
        $this->mockTaskService = \Mockery::mock(TaskServiceInterface::class);

        $this->service = new TaskCreationService(
            $this->mockRepository,
            $this->mockCycleService,
            $this->mockTaskService
        );

        // Create test user for authentication
        $role = Role::create(['name' => 'test', 'description' => 'Test role']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        Auth::login($this->user);
    });

    describe('createTask', function () {
        it('creates task without dependencies', function () {
            $taskData = [
                'description' => 'Test task',
                'status' => 'pending',
                'assignee_id' => $this->user->id,
            ];

            $mockTask = \Mockery::mock(Task::class);

            $this->mockRepository
                ->shouldReceive('create')
                ->once()
                ->with($taskData)
                ->andReturn($mockTask);

            $result = $this->service->createTask($taskData);

            expect($result)->toBe($mockTask);
        });

        it('creates task with dependencies', function () {
            $taskData = [
                'description' => 'Test task',
                'status' => 'pending',
                'assignee_id' => $this->user->id,
                'depends_on_id' => 1,
            ];

            $mockTask = \Mockery::mock(Task::class);
            $mockDependsOnTask = \Mockery::mock(Task::class);
            $mockRelation = \Mockery::mock(BelongsToMany::class);

            // Mock task creation
            $this->mockRepository
                ->shouldReceive('create')
                ->once()
                ->with($taskData)
                ->andReturn($mockTask);

            // Mock dependency validation and creation
            $mockTask->shouldReceive('getAttribute')->with('status')->andReturn('pending');

            $this->mockTaskService
                ->shouldReceive('getTaskOrFail')
                ->once()
                ->with(1)
                ->andReturn($mockDependsOnTask);

            $this->mockCycleService
                ->shouldReceive('wouldCreateCycle')
                ->once()
                ->with($mockTask, $mockDependsOnTask)
                ->andReturn(false);

            $mockTask->shouldReceive('dependsOnTasks')->andReturn($mockRelation);
            $mockRelation->shouldReceive('where')->with('depends_on_task_id', 1)->andReturnSelf();
            $mockRelation->shouldReceive('exists')->andReturn(false);
            $mockRelation->shouldReceive('attach')->once()->with(1);

            $result = $this->service->createTask($taskData);

            expect($result)->toBe($mockTask);
        });
    });

    describe('addDependency', function () {
        it('adds dependency successfully', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockDependsOnTask = \Mockery::mock(Task::class);
            $mockRelation = \Mockery::mock(BelongsToMany::class);
            $dependsOnId = 1;

            $mockTask->shouldReceive('getAttribute')->with('status')->andReturn('pending');

            $this->mockTaskService
                ->shouldReceive('getTaskOrFail')
                ->once()
                ->with($dependsOnId)
                ->andReturn($mockDependsOnTask);

            $this->mockCycleService
                ->shouldReceive('wouldCreateCycle')
                ->once()
                ->with($mockTask, $mockDependsOnTask)
                ->andReturn(false);

            $mockTask->shouldReceive('dependsOnTasks')->andReturn($mockRelation);
            $mockRelation->shouldReceive('where')->with('depends_on_task_id', $dependsOnId)->andReturnSelf();
            $mockRelation->shouldReceive('exists')->andReturn(false);
            $mockRelation->shouldReceive('attach')->once()->with($dependsOnId);

            $this->service->addDependency($mockTask, $dependsOnId);
        });

        it('throws exception when task is already completed', function () {
            $mockTask = \Mockery::mock(Task::class);
            $dependsOnId = 1;

            $mockTask->shouldReceive('getAttribute')->with('status')->andReturn('complete');

            expect(fn () => $this->service->addDependency($mockTask, $dependsOnId))
                ->toThrow(TaskAlreadyCompletedException::class);
        });

        it('throws exception when dependency would create cycle', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockDependsOnTask = \Mockery::mock(Task::class);
            $dependsOnId = 1;

            $mockTask->shouldReceive('getAttribute')->with('status')->andReturn('pending');

            $this->mockTaskService
                ->shouldReceive('getTaskOrFail')
                ->once()
                ->with($dependsOnId)
                ->andReturn($mockDependsOnTask);

            $this->mockCycleService
                ->shouldReceive('wouldCreateCycle')
                ->once()
                ->with($mockTask, $mockDependsOnTask)
                ->andReturn(true);

            expect(fn () => $this->service->addDependency($mockTask, $dependsOnId))
                ->toThrow(TaskCycleDependencyException::class);
        });

        it('throws exception when dependency already exists', function () {
            $mockTask = \Mockery::mock(Task::class);
            $mockDependsOnTask = \Mockery::mock(Task::class);
            $mockRelation = \Mockery::mock(BelongsToMany::class);
            $dependsOnId = 1;

            $mockTask->shouldReceive('getAttribute')->with('status')->andReturn('pending');

            $this->mockTaskService
                ->shouldReceive('getTaskOrFail')
                ->once()
                ->with($dependsOnId)
                ->andReturn($mockDependsOnTask);

            $this->mockCycleService
                ->shouldReceive('wouldCreateCycle')
                ->once()
                ->with($mockTask, $mockDependsOnTask)
                ->andReturn(false);

            $mockTask->shouldReceive('dependsOnTasks')->andReturn($mockRelation);
            $mockRelation->shouldReceive('where')->with('depends_on_task_id', $dependsOnId)->andReturnSelf();
            $mockRelation->shouldReceive('exists')->andReturn(true);

            expect(fn () => $this->service->addDependency($mockTask, $dependsOnId))
                ->toThrow(TaskDuplicateDependencyException::class);
        });
    });

    afterEach(function () {
        \Mockery::close();
    });
});
