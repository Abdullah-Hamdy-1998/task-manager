<?php

use App\Models\Role;
use App\Models\User;
use App\Services\TaskFilterService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('TaskFilterService', function () {
    beforeEach(function () {
        $this->service = new TaskFilterService;

        // Create test user for authentication
        $role = Role::create(['name' => 'test', 'description' => 'Test role']);
        $this->user = User::factory()->create(['role_id' => $role->id]);
        Auth::login($this->user);
    });

    describe('applyFilters', function () {
        it('applies no filters when filters array is empty', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = [];

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('applies status filter', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = ['status' => 'pending'];

            $mockQuery
                ->shouldReceive('byStatus')
                ->once()
                ->with('pending')
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('applies due date range filter with both dates', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = [
                'due_from' => '2024-01-01',
                'due_to' => '2024-12-31',
            ];

            $mockQuery
                ->shouldReceive('byDueDateRange')
                ->once()
                ->with('2024-01-01', '2024-12-31')
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('applies due date range filter with only from date', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = ['due_from' => '2024-01-01'];

            $mockQuery
                ->shouldReceive('byDueDateRange')
                ->once()
                ->with('2024-01-01', null)
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('applies due date range filter with only to date', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = ['due_to' => '2024-12-31'];

            $mockQuery
                ->shouldReceive('byDueDateRange')
                ->once()
                ->with(null, '2024-12-31')
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('applies assignee filter', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = ['assignee_id' => 123];

            $mockQuery
                ->shouldReceive('byAssignee')
                ->once()
                ->with(123)
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('applies multiple filters together', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = [
                'status' => 'completed',
                'due_from' => '2024-01-01',
                'due_to' => '2024-06-30',
                'assignee_id' => 456,
            ];

            $mockQuery
                ->shouldReceive('byStatus')
                ->once()
                ->with('completed')
                ->andReturnSelf();

            $mockQuery
                ->shouldReceive('byDueDateRange')
                ->once()
                ->with('2024-01-01', '2024-06-30')
                ->andReturnSelf();

            $mockQuery
                ->shouldReceive('byAssignee')
                ->once()
                ->with(456)
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('ignores unknown filter keys', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = [
                'status' => 'pending',
                'unknown_filter' => 'some_value',
                'another_unknown' => 123,
            ];

            $mockQuery
                ->shouldReceive('byStatus')
                ->once()
                ->with('pending')
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });

        it('handles null and empty string filter values', function () {
            $mockQuery = \Mockery::mock(Builder::class);
            $filters = [
                'status' => '',
                'assignee_id' => null,
                'due_from' => '',
            ];

            // Empty string should still trigger the filter
            $mockQuery
                ->shouldReceive('byStatus')
                ->once()
                ->with('')
                ->andReturnSelf();

            $mockQuery
                ->shouldReceive('byDueDateRange')
                ->once()
                ->with('', null)
                ->andReturnSelf();

            $result = $this->service->applyFilters($mockQuery, $filters);

            expect($result)->toBe($mockQuery);
        });
    });

    afterEach(function () {
        \Mockery::close();
    });
});
