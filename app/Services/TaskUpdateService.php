<?php

namespace App\Services;

use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskUpdateServiceInterface;
use App\Exceptions\TaskIncompleteDependenciesException;
use App\Models\Task;

class TaskUpdateService implements TaskUpdateServiceInterface
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function updateTask(Task $task, array $data): Task
    {
        $this->taskRepository->update($task, $data);

        return $task->fresh();
    }

    public function updateStatus(Task $task, string $newStatus): Task
    {
        if ($newStatus === 'completed' && $task->hasIncompleteDependencies()) {
            throw new TaskIncompleteDependenciesException;
        }

        $this->taskRepository->updateStatus($task, $newStatus);

        return $task->fresh();
    }
}
