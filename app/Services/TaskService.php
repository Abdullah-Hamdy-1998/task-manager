<?php

namespace App\Services;

use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Exceptions\TaskNotFoundException;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService implements TaskServiceInterface
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getAllTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getFilteredTasks($filters, $perPage);
    }

    public function getTask(int $id): ?Task
    {
        return $this->taskRepository->find($id);
    }

    public function getTaskOrFail(int $id): Task
    {
        $task = $this->taskRepository->find($id);

        if (! $task) {
            throw new TaskNotFoundException($id);
        }

        return $task;
    }
}
