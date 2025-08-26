<?php

namespace App\Repositories;

use App\Contracts\TaskFilterServiceInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository implements TaskRepositoryInterface
{
    protected $taskFilterService;

    public function __construct(TaskFilterServiceInterface $taskFilterService)
    {
        $this->taskFilterService = $taskFilterService;
    }

    public function create(array $data): Model
    {
        return Task::create($data);
    }

    public function find(int $id): ?Task
    {
        return Task::with(['dependsOnTasks', 'dependentTasks', 'assignee', 'creator'])->find($id);
    }

    public function getFilteredTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Task::query();
        $query = $this->taskFilterService->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    public function update(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function updateStatus(Task $task, string $newStatus): bool
    {
        $task->status = $newStatus;

        return $task->save();
    }
}
