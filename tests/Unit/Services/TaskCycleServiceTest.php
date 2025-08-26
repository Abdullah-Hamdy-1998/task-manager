<?php

use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskCycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('TaskCycleService', function () {
    beforeEach(function () {
        $this->service = new TaskCycleService;

        // Create test user for authentication
        $role = Role::create(['name' => 'test', 'description' => 'Test role']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        Auth::login($this->user);
    });

    describe('wouldCreateCycle', function () {
        it('returns true when task depends on itself', function () {
            $task = Task::factory()->create();

            $result = $this->service->wouldCreateCycle($task, $task);

            expect($result)->toBeTrue();
        });

        it('returns false when no cycle exists', function () {
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();

            // Mock DB::select to return empty result (no cycle)
            DB::shouldReceive('select')
                ->once()
                ->with(Mockery::type('string'), [$task2->id, $task1->id])
                ->andReturn([]);

            $result = $this->service->wouldCreateCycle($task1, $task2);

            expect($result)->toBeFalse();
        });

        it('returns true when cycle would be created', function () {
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();

            // Mock DB::select to return non-empty result (cycle detected)
            DB::shouldReceive('select')
                ->once()
                ->with(Mockery::type('string'), [$task2->id, $task1->id])
                ->andReturn([['task_id' => $task1->id]]);

            $result = $this->service->wouldCreateCycle($task1, $task2);

            expect($result)->toBeTrue();
        });

        it('uses correct SQL query structure', function () {
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();

            DB::shouldReceive('select')
                ->once()
                ->with(Mockery::on(function ($query) {
                    // Verify the query contains the expected CTE structure
                    return str_contains($query, 'WITH RECURSIVE dependency_chain') &&
                           str_contains($query, 'task_dependencies td') &&
                           str_contains($query, 'UNION ALL');
                }), [$task2->id, $task1->id])
                ->andReturn([]);

            $this->service->wouldCreateCycle($task1, $task2);
        });

        it('passes correct parameters to SQL query', function () {
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();

            DB::shouldReceive('select')
                ->once()
                ->with(Mockery::type('string'), [$task2->id, $task1->id])
                ->andReturn([]);

            $this->service->wouldCreateCycle($task1, $task2);
        });

        it('handles database exceptions gracefully', function () {
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();

            DB::shouldReceive('select')
                ->once()
                ->with(Mockery::type('string'), [$task2->id, $task1->id])
                ->andThrow(new \Exception('Database error'));

            expect(fn () => $this->service->wouldCreateCycle($task1, $task2))
                ->toThrow(\Exception::class, 'Database error');
        });
    });

    describe('integration tests', function () {
        it('detects simple cycle with real database', function () {
            // Create tasks with actual database
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();

            // Create dependency: task2 depends on task1
            $task2->dependsOnTasks()->attach($task1->id);

            // Now check if task1 depending on task2 would create a cycle
            $result = $this->service->wouldCreateCycle($task1, $task2);

            expect($result)->toBeTrue();
        });

        it('detects complex cycle with real database', function () {
            // Create a chain: task1 -> task2 -> task3
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();
            $task3 = Task::factory()->create();

            // task2 depends on task1
            $task2->dependsOnTasks()->attach($task1->id);
            // task3 depends on task2
            $task3->dependsOnTasks()->attach($task2->id);

            // Now check if task1 depending on task3 would create a cycle
            $result = $this->service->wouldCreateCycle($task1, $task3);

            expect($result)->toBeTrue();
        });

        it('allows valid dependency with real database', function () {
            $task1 = Task::factory()->create();
            $task2 = Task::factory()->create();
            $task3 = Task::factory()->create();

            // Create some unrelated dependencies
            $task2->dependsOnTasks()->attach($task1->id);

            // task3 depending on task1 should not create a cycle
            $result = $this->service->wouldCreateCycle($task3, $task1);

            expect($result)->toBeFalse();
        });
    });

    afterEach(function () {
        \Mockery::close();
    });
});
